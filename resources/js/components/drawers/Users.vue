<template>
  <div>
    <alert :options.sync="options" :show.sync="showAlert" @close="closeAlert" />
    <div class="action-panel-content">
      <div class="action-container" v-show="showListUser">
        <div class="action-heading">
          <h2>Users</h2>
          <form @submit.prevent="search" class="form-inline action-panel-search">
            <input
              id="plans-search"
              v-on:keyup="search"
              v-model="keyword"
              class="form-control"
              type="search"
              placeholder="Search"
              aria-label="Search"
              data-item="plans-search"
            />
          </form>
        </div>
        <table id="user-table" class="table table-striped asc">
          <thead>
            <tr>
              <th
                class="users-sort"
                :class="nameOrder"
                scope="col"
                @click="orderBy('name', nameOrderVal)"
              >Name</th>
              <th scope="col">Agency</th>
              <th scope="col">Role</th>
              <th v-if="isAdmin || isAgencyAdmin" class="action" scope="col">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td class="user-name">{{ user.name }}</td>
              <td v-if="user.agencies[0]">{{ user.agencies[0].name }}</td>
              <td v-if="!user.agencies[0]"></td>
              <td>{{ getRoles(user.roles[0].name) }}</td>
              <td v-if="isAdmin || isAgencyAdmin">
                <a class="editor-link" href="#" @click="editUser(user.id)">Edit</a>
                <a class="editor-link" href="#" @click="deleteUser(user.id)">Delete</a>
              </td>
            </tr>
          </tbody>
        </table>
        <button
          v-if="isAdmin || isAgencyAdmin"
          :disabled="userLimit <= userCount || isSuspend"
          id="add-user"
          @click="switchView('add')"
          class="btn btn-primary"
        >Add User</button>
      </div>
      <div class="action-container" v-show="showAddUser">
        <div class="action-heading">
          <h2>Add User</h2>
        </div>
        <form id="add-user" @submit.prevent="inviteUser">
          <div class="row sr-forms">
            <div class="col">
              <label for="email">Email</label>
              <input
                id="email"
                v-model="form.email"
                :class="{ 'is-invalid': form.errors.has('email') }"
                class="form-control"
                type="email"
                name="email"
              />
              <has-error :form="form" field="email" />
            </div>
            <div class="col"></div>
          </div>
          <div class="row sr-forms" v-if="isAdmin">
            <div class="col">
              <label for="agency">Agency</label>
              <select
                v-model="form.agency"
                :class="{ 'is-invalid': form.errors.has('agency') }"
                name="agency"
                id="agency"
                class="form-control"
              >
                <option value>--- Please Select ---</option>
                <option
                  v-for="agency in agencies"
                  :key="agency.id"
                  :value="agency.id"
                >{{ agency.name }}</option>
              </select>
              <has-error :form="form" field="agency" />
            </div>
            <div class="col"></div>
          </div>
          <div class="row sr-forms">
            <div class="col">
              <label for="email">Role</label>
              <select
                v-model="form.role"
                :class="{ 'is-invalid': form.errors.has('role') }"
                name="role"
                id="role"
                class="form-control"
              >
                <option value>--- Please Select ---</option>
                <option v-if="isAdmin" value="admin">Admin</option>
                <option value="agency-admin">Agency Admin</option>
                <option value="user">User</option>
              </select>
            </div>
            <div class="col"></div>
          </div>
          <button :loading="form.busy" class="btn btn-primary">Send Invite</button>
          <button
            class="btn btn-secondary"
            v-on:click.prevent="switchView('list')"
            id="cancel-btn"
          >Cancel</button>
        </form>
      </div>
      <div class="action-container" v-show="showEditUser">
        <div class="action-heading">
          <h2>Edit User</h2>
        </div>
        <form id="add-user" @submit.prevent="updateUser">
          <div class="row sr-forms">
            <div class="col">
              <label for="name">Name</label>
              <input
                id="name"
                :class="{ 'is-invalid': form.errors.has('name') }"
                v-model="form.name"
                type="text"
                class="form-control"
              />
              <has-error :form="form" field="name" />
            </div>
            <div class="col"></div>
          </div>
          <div class="row sr-forms">
            <div class="col">
              <label for="email">Email</label>
              <input
                id="email"
                :class="{ 'is-invalid': form.errors.has('email') }"
                v-model="form.email"
                type="email"
                class="form-control"
              />
              <has-error :form="form" field="email" />
            </div>
            <div class="col"></div>
          </div>
          <div class="row sr-forms" v-if="isAdmin">
            <div class="col">
              <label for="agency">Agency</label>
              <select
                v-model="form.agency"
                :class="{ 'is-invalid': form.errors.has('agency') }"
                name="agency"
                id="agency"
                class="form-control"
              >
                <option value>--- Please Select ---</option>
                <option
                  v-for="agency in agencies"
                  :key="agency.id"
                  :value="agency.id"
                >{{ agency.name }}</option>
              </select>
              <has-error :form="form" field="agency" />
            </div>
            <div class="col"></div>
          </div>
          <div class="row sr-forms">
            <div class="col">
              <label for="role">Role</label>
              <select
                v-model="form.role"
                :class="{ 'is-invalid': form.errors.has('role') }"
                name="role"
                id="role"
                class="form-control"
              >
                <option value>--- Please Select ---</option>
                <option v-if="isAdmin" value="admin">Admin</option>
                <option value="agency-admin">Agency Admin</option>
                <option value="user">User</option>
              </select>
              <has-error :form="form" field="role" />
            </div>
            <div class="col"></div>
          </div>
          <button :loading="form.busy" class="btn btn-primary">Save</button>
          <button
            class="btn btn-secondary"
            v-on:click.prevent="switchView('list')"
            id="cancel-btn"
          >Cancel</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
import axios from "axios";
import Form from "vform";
import Alert from "../Alert";

export default {
  name: "Users",

  computed: mapGetters({
    authUser: "auth/user",
    billingStatus: "billing/status"
  }),

  components: {
    Alert
  },

  data: () => ({
    isAdmin: false,
    isAgencyAdmin: false,
    isUser: false,
    users: [],
    agencies: [],
    showListUser: true,
    showAddUser: false,
    showEditUser: false,
    options: {},
    showAlert: false,
    keyword: "",
    nameOrder: {
      desc: true,
      asc: false
    },
    nameOrderVal: "desc",

    form: new Form({
      email: "",
      role: ""
    })
  }),

  methods: {
    switchView(view) {
      this.showListUser = false;
      this.showAddUser = false;
      this.showEditUser = false;

      if (view === "list") this.showListUser = true;
      else if (view === "add") {
        this.showAddUser = true;
        this.form.reset();
      } else if (view === "edit") this.showEditUser = true;
    },

    async getUsers(params) {
      try {
        const response = await axios.get("/api/v1/users", { params });
        this.users = response.data.data;
      } catch (err) {
        this.showAlert = true;
        this.options.content = err.response.data.message;
        this.options.type = "danger";
      }
    },

    async getAgencies(params) {
      try {
        const response = await axios.get("/api/v1/agencies", { params });
        this.agencies = response.data.data;
      } catch (err) {
        this.showAlert = true;
        this.options.content = err.response.data.message;
        this.options.type = "danger";
      }
    },

    async getUserById(id) {
      try {
        const response = await axios.get(
          "/api/v1/users/" + id + "?include=roles,agencies"
        );
        this.form.id = response.data.id;
        this.form.name = response.data.name;
        this.form.email = response.data.email;
        this.form.role = response.data.roles[0].name;
        this.form.agency = response.data.agencies[0].id;
      } catch (err) {
        this.showAlert = true;
        this.options.content = err.response.data.message;
        this.options.type = "danger";
      }
    },

    editUser(id) {
      this.switchView("edit");
      this.getUserById(id);
    },

    getRoles(roles) {
      if (roles === "admin") return "Admin";
      else if (roles === "agency-admin") return "Agency Admin";
      else return "User";
    },

    inviteUser() {
      this.form
        .post("/api/v1/users/invite")
        .then(res => {
          this.showAlert = true;
          this.options.content = "Invitation sent";
          this.options.type = "success";

          this.switchView("list");
          this.form.reset();

          this.getUsers({ include: "roles,agencies" });
        })
        .catch(err => {
          this.showAlert = true;
          this.options.content = err.response.data.message;
          this.options.type = "danger";
        });
    },

    updateUser() {
      this.form
        .put("/api/v1/users/" + this.form.id)
        .then(res => {
          this.showAlert = true;
          this.options.content = "The user was updated";
          this.options.type = "success";

          this.switchView("list");
          this.form.reset();

          this.getUsers({ include: "roles,agencies" });
        })
        .catch(err => {
          this.showAlert = true;
          this.options.content = err.response.data.message;
          this.options.type = "danger";
        });
    },

    deleteUser(id) {
      this.$swal({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "No, cancel!",
        reverseButtons: true
      }).then(result => {
        if (result.value) {
          axios
            .delete("/api/v1/users/" + id)
            .then(() => {
              this.getUsers({ include: "roles,agencies" });

              this.$swal("Deleted!", "The user has been deleted.", "success");
            })
            .catch(err => {
              this.showAlert = true;
              this.options.content = err.response.data.message;
              this.options.type = "danger";
            });
        } else if (result.dismiss === "cancel") {
          this.$swal("Cancelled", "The user is safe :)", "error");
        }
      });
    },

    async search() {
      if (this.keyword.length > 1) {
        const response = await axios.get(
          "/api/v1/users/search/" + this.keyword + "?include=roles,agencies"
        );
        this.users = response.data.data;
      } else {
        this.getUsers({ include: "roles,agencies" });
      }
    },

    closeAlert() {
      this.options = {};
      this.showAlert = false;
    },

    orderBy(orderBy, orderVal) {
      if (orderBy == "name") {
        if (orderVal == "desc") {
          this.nameOrderVal = "asc";
          this.nameOrder = {
            desc: true,
            asc: false
          };
          this.getUsers({
            include: "roles,agencies",
            order_name: "name",
            order_value: orderVal
          });
        } else if (orderVal == "asc") {
          this.nameOrderVal = "desc";
          this.nameOrder = {
            desc: false,
            asc: true
          };
          this.getUsers({
            include: "roles,agencies",
            order_name: "name",
            order_value: orderVal
          });
        }
      }
    }
  },

  beforeMount: function() {
    this.isAdmin = this.authUser.roles.name == "admin" ? true : false;
    this.isAgencyAdmin =
      this.authUser.roles.name == "agency-admin" ? true : false;
    this.isUser = this.authUser.roles.name == "user" ? true : false;
    this.isSuspend = this.authUser.agencies.status == "suspend" ? true : false;
    this.userCount = this.authUser.agencies.users_count;
    this.userLimit = this.authUser.agencies.plans.user_limit;

    if (this.isAgencyAdmin && this.billingStatus == "suspend") {
      this.isSuspend = true;
    } else if (this.isUser && this.billingStatus == "suspend") {
      this.isSuspend = true;
    }
  },

  mounted: function() {
    this.getUsers({ include: "roles,agencies" });
    this.getAgencies({});
  }
};
</script>
