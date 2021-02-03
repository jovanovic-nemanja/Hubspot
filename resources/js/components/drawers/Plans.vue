<template>
  <div>
    <alert :options.sync="options" :show.sync="showAlert" @close="closeAlert" />
    <div class="action-panel-content">
      <div class="action-container" v-show="showListPlan">
        <div class="action-heading">
          <h2>Plans</h2>
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
        <table id="plan-table" class="table table-striped asc">
          <thead>
            <tr>
              <th :class="nameOrder" scope="col" @click="orderBy('name', nameOrderVal)">Plan Name</th>
              <th scope="col">Type</th>
              <th scope="col">Portals</th>
              <th scope="col">Users</th>
              <th scope="col">Status</th>
              <th v-if="isAdmin" class="action" scope="col">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="plan in plans" :key="plan.id">
              <td class="plan-name">{{ plan.name }}</td>
              <td v-if="plan.type == 'free'">Free</td>
              <td v-if="plan.type == 'paid'">Subscription</td>
              <td v-if="plan.type == 'fixed'">One-off Payment</td>
              <td>{{ plan.portal_limit }}</td>
              <td>{{ plan.user_limit }}</td>
              <td v-if="plan.status">Active</td>
              <td v-if="!plan.status">Inactive</td>
              <td v-if="isAdmin">
                <a class="editor-link" href="#" @click="editPlan(plan.id)">Edit</a>
                <a class="editor-link" href="#" @click="deletePlan(plan.id)">Delete</a>
              </td>
            </tr>
          </tbody>
        </table>
        <button
          v-if="isAdmin"
          id="add-plan"
          @click="switchView('add')"
          class="btn btn-primary"
        >Add Plan</button>
      </div>
      <div class="action-container" v-show="showAddPlan">
        <div class="action-heading">
          <h2>Add Plan</h2>
        </div>
        <form id="add-plan" @submit.prevent="createPlan">
          <div class="row sr-forms">
            <div class="col">
              <label for="name">Name</label>
              <input
                id="name"
                :class="{ 'is-invalid': form.errors.has('name') }"
                type="text"
                class="form-control"
                v-model="form.name"
              />
            </div>
            <div class="col">
              <label for="type">Type</label>
              <select
                name="type"
                :class="{ 'is-invalid': form.errors.has('type') }"
                id="type"
                class="form-control"
                v-model="form.type"
                v-on:change="checkType"
              >
                <option disabled value>--- Please Select ---</option>
                <option value="free">Free</option>
                <option value="paid">Subscription</option>
                <option value="fixed">One-off Payment</option>
              </select>
            </div>
          </div>
          <free :form.sync="form" v-if="isFreePlan" />
          <paid :form.sync="form" v-if="isPaidPlan" />
          <fixed :form.sync="form" v-if="isFixedPlan" />
          <button :disabled="form.busy" type="submit" class="btn btn-primary">Save</button>
          <button
            class="btn btn-secondary"
            id="cancel-btn"
            v-on:click.prevent="switchView('list')"
          >Cancel</button>
        </form>
      </div>
      <div class="action-container" v-show="showEditPlan">
        <div class="action-heading">
          <h2>Edit Plan</h2>
        </div>
        <form id="add-plan" @submit.prevent="updatePlan">
          <div class="row sr-forms">
            <div class="col">
              <label for="name">Name</label>
              <input
                id="name"
                :class="{ 'is-invalid': form.errors.has('name') }"
                type="text"
                class="form-control"
                v-model="form.name"
              />
            </div>
            <div class="col">
              <label for="type">Type</label>
              <select
                name="type"
                :class="{ 'is-invalid': form.errors.has('type') }"
                id="type"
                class="form-control"
                v-model="form.type"
                v-on:change="checkType"
              >
                <option disabled value>--- Please Select ---</option>
                <option value="free">Free</option>
                <option value="paid">Subscription</option>
                <option value="fixed">One-off Payment</option>
              </select>
            </div>
          </div>
          <free :form.sync="form" v-if="isFreePlan" />
          <paid :form.sync="form" v-if="isPaidPlan" />
          <fixed :form.sync="form" v-if="isFixedPlan" />
          <button :disabled="form.busy" type="submit" class="btn btn-primary">Save</button>
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
import axios from "axios";
import Form from "vform";
import Alert from "../Alert";
import Free from "../forms/plans/free";
import Paid from "../forms/plans/paid";
import Fixed from "../forms/plans/fixed";

export default {
  name: "Plans",

  computed: mapGetters({
    authUser: "auth/user"
  }),

  components: {
    Alert,
    Free,
    Paid,
    Fixed
  },

  data: () => ({
    isAdmin: false,
    isAgencyAdmin: false,
    isUser: false,
    plans: [],
    showListPlan: true,
    showAddPlan: false,
    showEditPlan: false,
    isFreePlan: true,
    isPaidPlan: false,
    isFixedPlan: false,
    options: {},
    showAlert: false,
    keyword: "",
    nameOrder: {
      desc: true,
      asc: false
    },
    nameOrderVal: "desc",

    form: new Form({
      name: "",
      type: "",
      portal_limit: "",
      user_limit: "",
      module_limit: "",
      is_free_module: "",
      is_hidden_plan: "",
      price: "",
      currency: "",
      interval: "",
      trial_period: "",
      custom_logo: "",
      stripe_plan_id: "",
      status: ""
    })
  }),

  methods: {
    switchView(view) {
      this.showListPlan = false;
      this.showAddPlan = false;
      this.showEditPlan = false;
      this.isFreePlan = false;
      this.isPaidPlan = false;
      this.isFixedPlan = false;

      if (view === "list") this.showListPlan = true;
      else if (view === "add") {
        this.showAddPlan = true;
        this.form.reset();
      } else if (view === "edit") this.showEditPlan = true;
    },

    editPlan(id) {
      this.switchView("edit");
      this.getPlanById(id);
    },

    checkType(event) {
      this.isFreePlan = false;
      this.isPaidPlan = false;
      this.isFixedPlan = false;

      if (event.target.value == "paid") this.isPaidPlan = true;
      else if (event.target.value == "fixed") this.isFixedPlan = true;
      else this.isFreePlan = true;
    },

    async getPlans(params) {
      const response = await axios.get("/api/v1/plans", { params });
      this.plans = response.data.data;
    },

    async getPlanById(id) {
      const response = await axios.get("/api/v1/plans/" + id);

      this.form.id = response.data.id;
      this.form.name = response.data.name;
      this.form.type = response.data.type;
      this.form.portal_limit = response.data.portal_limit;
      this.form.user_limit = response.data.user_limit;
      this.form.module_limit = response.data.module_limit;
      this.form.is_free_module = response.data.is_free_module;
      this.form.is_hidden_plan = response.data.is_hidden_plan;
      this.form.price = response.data.price;
      this.form.currency = response.data.currency;
      this.form.interval = response.data.interval;
      this.form.trial_period = response.data.trial_period;
      this.form.custom_logo = response.data.custom_logo;
      this.form.stripe_plan_id = response.data.stripe_plan_id;
      this.form.status = response.data.status;
      this.form.agency_plan = response.data.agency_plan;

      if (this.form.type == "paid") this.isPaidPlan = true;
      else if (this.form.type == "fixed") this.isFixedPlan = true;
      else this.isFreePlan = true;
    },

    createPlan() {
      this.form
        .post("/api/v1/plans")
        .then(res => {
          this.showAlert = true;
          this.options.content = "The plan was created";
          this.options.type = "success";

          this.switchView("list");
          this.form.reset();

          this.getPlans();
        })
        .catch(err => {
          this.showAlert = true;
          this.options.content = err.response.data.message;
          this.options.type = "danger";
        });
    },

    updatePlan() {
      this.form
        .put("/api/v1/plans/" + this.form.id)
        .then(res => {
          this.showAlert = true;
          this.options.content = "The plan was updated";
          this.options.type = "success";

          this.switchView("list");
          this.form.reset();

          this.getPlans();
        })
        .catch(err => {
          this.showAlert = true;
          this.options.content = err.response.data.message;
          this.options.type = "danger";
        });
    },

    deletePlan(id) {
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
            .delete("/api/v1/plans/" + id)
            .then(() => {
              this.getPlans();

              this.$swal("Deleted!", "The plan has been deleted.", "success");
            })
            .catch(err => {
              this.showAlert = true;
              this.options.content = err.response.data.message;
              this.options.type = "danger";
            });
        } else if (result.dismiss === "cancel") {
          this.$swal("Cancelled", "The plan is safe :)", "error");
        }
      });
    },

    async search() {
      if (this.keyword.length > 1) {
        const response = await axios.get(
          "/api/v1/plans/search/" + this.keyword
        );
        this.plans = response.data.data;
      } else {
        this.getPlans();
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
          this.getPlans({
            order_name: "name",
            order_value: orderVal
          });
        } else if (orderVal == "asc") {
          this.nameOrderVal = "desc";
          this.nameOrder = {
            desc: false,
            asc: true
          };
          this.getPlans({
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
  },

  mounted: function() {
    this.getPlans();
  }
};
</script>
