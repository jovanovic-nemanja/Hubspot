<template>
  <div>
    <alert :options.sync="options" :show.sync="showAlert" @close="closeAlert" />
    <div class="action-panel-content">
      <div class="action-container" v-show="showListAgency">
        <div class="action-heading">
          <h2>Agencies</h2>
          <form @submit.prevent="search" class="form-inline action-panel-search">
            <input
              id="agency-search"
              v-on:keyup="search"
              v-model="keyword"
              class="form-control"
              type="search"
              placeholder="Search"
              aria-label="Search"
              data-item="module-search"
            />
          </form>
        </div>
        <table id="agency-table" class="table table-striped dec">
          <thead>
            <tr>
              <th :class="nameOrder" scope="col" @click="orderBy('name', nameOrderVal)">Agency Name</th>
              <th scope="col">Plan</th>
              <th scope="col">Users</th>
              <th scope="col">Portals</th>
              <th v-if="isAdmin" class="action" scope="col">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="agency in agencies" :key="agency.id">
              <td class="agency-name">{{ agency.name }}</td>
              <td v-if="agency.plans.length > 0">{{ agency.plans[0].name }}</td>
              <td v-if="agency.plans.length <= 0">No Plan</td>
              <td>{{ agency.users_count }}</td>
              <td>{{ agency.portals_count }}</td>
              <td v-if="isAdmin">
                <a class="editor-link" href="#" @click="editAgency(agency.id)">Edit</a>
                <a class="editor-link" href="#" @click="deleteAgency(agency.id)">Delete</a>
              </td>
            </tr>
          </tbody>
        </table>
        <button
          v-if="isAdmin"
          id="add-agency"
          type="submit"
          class="btn btn-primary"
          @click="switchView('add')"
        >Add Agency</button>
      </div>
      <div class="action-container" v-show="showAddAgency">
        <div class="action-heading">
          <h2>Add Agency</h2>
        </div>
        <form id="add-agency" @submit.prevent="createAgency">
          <div class="row sr-forms">
            <div class="col">
              <label for="name">Agency Name</label>
              <input
                id="name"
                :class="{ 'is-invalid': form.errors.has('name') }"
                type="text"
                class="form-control"
                v-model="form.name"
              />
              <has-error :form="form" field="name" />
            </div>
            <div class="col">
              <label for="files">Logo</label>
              <img :src="form.cover" class="mb-1" style="height: 55px" />
              <input
                id="files"
                v-on:change="onImageChange"
                type="file"
                class="add-file"
                style="color:transparent;"
              />
              <label id="fileLabel">Choose file</label>
            </div>
          </div>
          <div class="row sr-forms">
            <div class="col">
              <label for="admin_name">Admin Name</label>
              <input
                id="admin_name"
                :class="{ 'is-invalid': form.errors.has('admin_name') }"
                type="text"
                class="form-control"
                v-model="form.admin_name"
              />
              <has-error :form="form" field="admin_name" />
            </div>
            <div class="col"></div>
          </div>
          <div class="row sr-forms">
            <div class="col">
              <label for="admin_email">Admin Email</label>
              <input
                id="admin_email"
                :class="{ 'is-invalid': form.errors.has('admin_email') }"
                type="email"
                class="form-control"
                v-model="form.admin_email"
              />
              <has-error :form="form" field="admin_email" />
            </div>
            <div class="col"></div>
          </div>
          <div class="row sr-forms" v-if="selectedPlan.type == 'fixed'">
            <div class="col">
              <label for="paid_in_advance">Mark as Paid</label>
              <input
                id="paid_in_advance"
                :class="{ 'is-invalid': form.errors.has('paid_in_advance') }"
                type="checkbox"
                class="form-control"
                v-model="form.paid_in_advance"
              />
              <has-error :form="form" field="paid_in_advance" />
            </div>
            <div class="col"></div>
          </div>
          <div class="row sr-forms">
            <div class="col">
              <label for="agency_plan">Agency Account</label>
              <input
                id="agency_plan"
                :class="{ 'is-invalid': form.errors.has('agency_plan') }"
                type="checkbox"
                class="form-control"
                @change="filterPlan()"
                v-model="form.agency_plan"
              />
              <has-error :form="form" field="agency_plan" />
            </div>
            <div class="col"></div>
          </div>
          <div class="row sr-forms">
            <div class="col plan">
              <label class="billing-plan" for="billing-plan">Plan</label>
            </div>
          </div>
          <div class="row sr-forms custom-box-radio">
            <div v-for="plan in plans" :key="plan.id">
              <input
                :id="plan.id"
                type="radio"
                name="billing-plan"
                v-model="form.plan_id"
                v-bind:value="plan.id"
                @change="selectPlan(plan)"
              />
              <label
                class="box-plan"
                :class="{ unlimited: plan.is_hidden_plan == 'yes' }"
                :for="plan.id"
              >
                <span class="plan-headline" v-if="plan.price == 0 || plan.price == null">Free</span>
                <span class="plan-headline" v-if="plan.price > 0 && plan.type != 'fixed'">
                  {{ plan.currency_symbol }} {{ plan.price | numFormat(0) }} /
                  <span
                    v-if="plan.interval == 'monthly'"
                  >mo</span>
                  <span v-else>year</span>
                </span>
                <span
                  class="plan-headline"
                  v-if="plan.price > 0 && plan.type == 'fixed'"
                >{{ plan.currency_symbol }} {{ plan.price | numFormat(0) }}</span>
                <span class="plan-includes">
                  <strong>{{ plan.name }}</strong>
                  <br />
                  {{ plan.portal_limit }} Portal
                  <br />
                  {{ plan.user_limit }} User
                  <br />
                  {{ plan.module_limit }} Modules
                </span>
              </label>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Save</button>
          <button
            class="btn btn-secondary"
            id="cancel-btn"
            v-on:click.prevent="switchView('list')"
          >Cancel</button>
        </form>
      </div>
      <div class="action-container" v-show="showEditAgency">
        <div class="action-heading">
          <h2>Edit Agency</h2>
        </div>
        <form id="add-agency" @submit.prevent="updateAgency">
          <div class="row sr-forms">
            <div class="col">
              <label for="name">Agency Name</label>
              <input
                id="name"
                :class="{ 'is-invalid': form.errors.has('name') }"
                type="text"
                class="form-control"
                v-model="form.name"
              />
              <has-error :form="form" field="name" />
            </div>
            <div class="col">
              <label for="files">Logo</label>
              <img :src="form.cover" class="mb-1" style="height: 55px" />
              <input
                id="files"
                v-on:change="onImageChange"
                type="file"
                class="add-file"
                style="color:transparent;"
              />
              <label id="fileLabel">Choose file</label>
            </div>
          </div>
          <div class="row sr-forms">
            <div class="col">
              <label for="admin_name">Admin Name</label>
              <input
                id="admin_name"
                :class="{ 'is-invalid': form.errors.has('admin_name') }"
                type="text"
                class="form-control"
                v-model="form.admin_name"
              />
              <has-error :form="form" field="admin_name" />
            </div>
            <div class="col"></div>
          </div>
          <div class="row sr-forms">
            <div class="col">
              <label for="admin_email">Admin Email</label>
              <input
                id="admin_email"
                :class="{ 'is-invalid': form.errors.has('admin_email') }"
                type="email"
                class="form-control"
                v-model="form.admin_email"
              />
              <has-error :form="form" field="admin_email" />
            </div>
            <div class="col"></div>
          </div>
          <div class="row sr-forms">
            <div class="col">
              <label for="admin_email">Agency Status</label>
              <select
                name="status"
                :class="{ 'is-invalid': form.errors.has('status') }"
                id="status"
                class="form-control"
                v-model="form.status"
              >
                <option disabled value>--- Please Select ---</option>
                <option value="active">Active</option>
                <option value="suspend">Suspend</option>
              </select>
              <has-error :form="form" field="status" />
            </div>
            <div class="col"></div>
          </div>
          <div class="row sr-forms" v-if="selectedPlan.type == 'fixed'">
            <div class="col">
              <label for="paid_in_advance">Mark as Paid</label>
              <input
                id="paid_in_advance"
                :class="{ 'is-invalid': form.errors.has('paid_in_advance') }"
                type="checkbox"
                class="form-control"
                v-model="form.paid_in_advance"
              />
              <has-error :form="form" field="paid_in_advance" />
            </div>
            <div class="col"></div>
          </div>
          <div class="row sr-forms">
            <div class="col">
              <label for="agency_plan">Agency Account</label>
              <input
                id="agency_plan"
                :class="{ 'is-invalid': form.errors.has('agency_plan') }"
                type="checkbox"
                class="form-control"
                @change="filterPlan()"
                v-model="form.agency_plan"
              />
              <has-error :form="form" field="agency_plan" />
            </div>
            <div class="col"></div>
          </div>
          <div class="row sr-forms">
            <div class="col plan">
              <label class="billing-plan" for="billing-plan">Plan</label>
            </div>
          </div>
          <div class="row sr-forms custom-box-radio">
            <div v-for="plan in plans" :key="plan.id">
              <input
                :id="plan.id"
                type="radio"
                name="billing-plan"
                v-model="form.plan_id"
                v-bind:value="plan.id"
                :checked="plan.id == form.plan_id"
                @change="selectPlan(plan)"
              />
              <label
                class="box-plan"
                :class="{ unlimited: plan.is_hidden_plan == 'yes' }"
                :for="plan.id"
              >
                <span class="plan-headline" v-if="plan.price == 0 || plan.price == null">Free</span>
                <span class="plan-headline" v-if="plan.price > 0 && plan.type != 'fixed'">
                  {{ plan.currency_symbol }} {{ plan.price | numFormat(0) }} /
                  <span
                    v-if="plan.interval == 'monthly'"
                  >mo</span>
                  <span v-else>year</span>
                </span>
                <span
                  class="plan-headline"
                  v-if="plan.price > 0 && plan.type == 'fixed'"
                >{{ plan.currency_symbol }} {{ plan.price | numFormat(0) }}</span>
                <span class="plan-includes">
                  <strong>{{ plan.name }}</strong>
                  <br />
                  {{ plan.portal_limit }} Portal
                  <br />
                  {{ plan.user_limit }} User
                  <br />
                  {{ plan.module_limit }} Modules
                </span>
              </label>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Save</button>
          <button
            class="btn btn-secondary"
            id="cancel-btn"
            v-on:click.prevent="switchView('list')"
          >Cancel</button>
        </form>
      </div>
    </div>
  </div>
</template>
<script>
import { mapGetters } from "vuex";
import Form from "vform";
import axios from "axios";
import Alert from "../Alert";

export default {
  name: "Agencies",

  computed: mapGetters({
    authUser: "auth/user"
  }),

  components: {
    Alert
  },

  data: () => ({
    isAdmin: false,
    isAgencyAdmin: false,
    isUser: false,
    showListAgency: true,
    showAddAgency: false,
    showEditAgency: false,
    agencies: [],
    plans: [],
    options: {},
    showAlert: false,
    keyword: "",
    nameOrder: {
      desc: true,
      asc: false
    },
    nameOrderVal: "desc",
    selectedPlan: {},

    form: new Form({
      name: "",
      admin_name: "",
      admin_email: "",
      status: "",
      plan_id: "",
      cover: "",
      agency_plan: "",
      paid_in_advance: ""
    })
  }),

  methods: {
    switchView(view) {
      this.showListAgency = false;
      this.showAddAgency = false;
      this.showEditAgency = false;

      if (view === "list") this.showListAgency = true;
      else if (view === "add") {
        this.showAddAgency = true;
        this.form.reset();
      } else if (view === "edit") this.showEditAgency = true;
    },

    editAgency(id) {
      this.switchView("edit");
      this.getAgencyById(id);
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

    async getAgencyById(id) {
      try {
        const response = await axios.get(
          "/api/v1/agencies/" + id + "?include=plans"
        );
        this.form.id = response.data.id;
        this.form.name = response.data.name;
        this.form.admin_name = response.data.admin_name;
        this.form.admin_email = response.data.admin_email;
        this.form.status = response.data.status;
        this.form.plan_id = response.data.plan_id;
        this.form.cover = response.data.cover;
        this.form.paid_in_advance = response.data.paid_in_advance;
        this.form.agency_plan = response.data.agency_plan;
        this.currentPlan = this.form.plan_id;
      } catch (err) {
        this.showAlert = true;
        this.options.content = err.response.data.message;
        this.options.type = "danger";
      }
    },

    async getPlans(params) {
      try {
        const response = await axios.get("/api/v1/plans", { params });
        this.plans = response.data.data;
      } catch (err) {
        this.showAlert = true;
        this.options.content = err.response.data.message;
        this.options.type = "danger";
      }
    },

    async filterPlan() {
      if (this.form.agency_plan) {
        this.getPlans({ agency_plan: "active" });
      } else {
        this.getPlans({});
      }
    },

    createAgency() {
      this.showAlert = false;
      this.options = {};

      this.form
        .post("/api/v1/agencies")
        .then(res => {
          this.showAlert = true;
          this.options.content = "The agency was created";
          this.options.type = "success";

          this.switchView("list");
          this.form.reset();

          this.getAgencies({
            include: "plans"
          });
        })
        .catch(err => {
          this.showAlert = true;
          this.options.content = err.response.data.message;
          this.options.type = "danger";
        });
    },

    updateAgency() {
      this.showAlert = false;
      this.options = {};

      this.form
        .put("/api/v1/agencies/" + this.form.id)
        .then(res => {
          this.showAlert = true;
          this.options.content = "The agency was updated";
          this.options.type = "success";

          this.switchView("list");
          this.form.reset();

          this.getAgencies({
            include: "plans"
          });
        })
        .catch(err => {
          this.showAlert = true;
          this.options.content = err.response.data.message;
          this.options.type = "danger";
        });
    },

    deleteAgency(id) {
      this.showAlert = false;
      this.options = {};

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
            .delete("/api/v1/agencies/" + id)
            .then(() => {
              this.getAgencies({
                include: "plans"
              });

              this.$swal("Deleted!", "The agency has been deleted.", "success");
            })
            .catch(err => {
              this.showAlert = true;
              this.options.content = err.response.data.message;
              this.options.type = "danger";
            });
        } else if (result.dismiss === "cancel") {
          this.$swal("Cancelled", "The agency is safe :)", "error");
        }
      });
    },

    async search() {
      if (this.keyword.length > 1) {
        const response = await axios.get(
          "/api/v1/agencies/search/" + this.keyword + "?include=plans"
        );
        this.agencies = response.data.data;
      } else {
        this.getAgencies({
          include: "plans"
        });
      }
    },

    onImageChange(e) {
      let currentObj = this;

      this.filename = e.target.files[0].name;

      const config = {
        headers: { "content-type": "multipart/form-data" }
      };

      let formData = new FormData();
      formData.append("image", e.target.files[0]);

      axios
        .post("/api/v1/image-upload", formData, config)
        .then(res => {
          currentObj.form.cover = res.data.image_name;
        })
        .catch(err => {
          this.showAlert = true;
          this.options.content = err.response.data.message;
          this.options.type = "danger";
        });
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
          this.getAgencies({
            include: "plans",
            order_name: "name",
            order_value: orderVal
          });
        } else if (orderVal == "asc") {
          this.nameOrderVal = "desc";
          this.nameOrder = {
            desc: false,
            asc: true
          };
          this.getAgencies({
            include: "plans",
            order_name: "name",
            order_value: orderVal
          });
        }
      }
    },

    selectPlan(plan) {
      this.selectedPlan = plan;
    }
  },

  beforeMount: function() {
    this.isAdmin = this.authUser.roles.name == "admin" ? true : false;
    this.isAgencyAdmin =
      this.authUser.roles.name == "agency-admin" ? true : false;
    this.isUser = this.authUser.roles.name == "user" ? true : false;
  },

  mounted: function() {
    this.getPlans({});
    this.getAgencies({
      include: "plans"
    });
  }
};
</script>
