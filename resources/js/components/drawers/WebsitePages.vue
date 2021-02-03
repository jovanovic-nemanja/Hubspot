<template>
  <div>
    <alert :options.sync="options" :show.sync="showAlert" @close="closeAlert" />
    <div class="action-panel-content">
      <div class="action-container">
        <div class="action-heading">
          <h2>Website Pages</h2>
          <form @submit.prevent="search" class="form-inline action-panel-search">
            <input
              id="website-search"
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
        <div class="sr-cards-view">
          <div class="sr-card" v-for="(module, index) in modules" :key="module.name">
            <img v-bind:src="module.module_preview" />
            <div class="sr-card-info">
              <span class="module-name">{{ module.name }}</span>
              <button
                v-bind:disabled="(!module.active || isSuspend) || (!isEligible && !module.modules.is_free)"
                @click="installModule(module.modules, index)"
                type="button"
                class="btn-sm btn-primary"
              >
                <span
                  v-if="!isEligible && !module.modules.is_free"
                  style="display: inline-block;"
                  class="fa fa-lock"
                  aria-hidden="true"
                  title="Module Group"
                ></span>
                {{ module.button_state }}
              </button>
            </div>
          </div>
        </div>
        <div class="website-search-results"></div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { mapGetters } from "vuex";
import Alert from "../Alert";

export default {
  name: "WebsitePages",

  computed: mapGetters({
    authUser: "auth/user",
    billingStatus: "billing/status"
  }),

  components: {
    Alert
  },

  data: () => ({
    modules: [],
    options: {},
    keyword: "",
    showAlert: false,
    isEligible: false
  }),

  methods: {
    getModules(params) {
      if (this.$route.params.id !== undefined) {
        axios
          .get("/api/v1/modules/website-pages", { params })
          .then(res => {
            this.modules = res.data.data;

            for (let index = 0; index < this.modules.length; index++) {
              this.modules[index].button_state = "Add Page";
              this.modules[index].active = true;
            }
          })
          .catch(err => {
            this.showAlert = true;
            this.options.content = err.response.data.message;
            this.options.type = "danger";
          });
      } else {
        this.showAlert = true;
        this.options.content = "Please select portal";
        this.options.type = "warning";
      }
    },

    installModule(module, index) {
      this.$swal({
        title: "Select Page Type",
        icon: "info",
        showCancelButton: true,
        confirmButtonText: "Website Page",
        cancelButtonText: "Landing Page",
        reverseButtons: true
      }).then(result => {
        if (result.value) {
          this.confirmInstallModule(module, index, "site_page");
        } else if (result.dismiss === "cancel") {
          this.confirmInstallModule(module, index, "landing_page");
        }
      });
    },

    confirmInstallModule(module, index, subcategory) {
      this.closeAlert();
      this.modules[index].button_state = "Adding Page...";
      this.modules[index].active = false;

      const pageData = {
        portal_id: this.$route.params.id,
        name: module.name,
        subcategory: subcategory,
        type: "website",
        module: module
      };

      axios
        .post("/api/v1/pages/custom", pageData)
        .then(res => {
          localStorage.customPageData = JSON.stringify(res.data);

          this.$router.push({ name: "custom-page-add-complete" });
        })
        .catch(err => {
          this.showAlert = true;
          this.options.content = err.response.data.message;
          this.options.type = "danger";
          this.modules[index].button_state = "Add Page";
          this.modules[index].active = true;
        });
    },

    search() {
      if (this.keyword.length > 1) {
        this.getModules({
          keyword: this.keyword
        });
      } else {
        this.getModules();
      }
    },

    closeAlert() {
      this.options = {};
      this.showAlert = false;
    }
  },

  beforeMount: function() {
    this.getModules();

    this.isAdmin = this.authUser.roles.name == "admin" ? true : false;
    this.isAgencyAdmin =
      this.authUser.roles.name == "agency-admin" ? true : false;
    this.isUser = this.authUser.roles.name == "user" ? true : false;
    this.isSuspend = this.authUser.agencies.status == "suspend" ? true : false;

    if (
      (this.isAgencyAdmin || this.isUser) &&
      this.billingStatus == "suspend"
    ) {
      this.isSuspend = true;
    } else {
      if (
        ((this.authUser.agencies.plans.type == "paid" ||
          this.authUser.agencies.plans.type == "fixed") &&
          !this.isSuspend) ||
        (this.authUser.agencies.plans.type == "free" &&
          this.authUser.agencies.plans.is_hidden_plan == "yes" &&
          !this.isSuspend)
      ) {
        this.isEligible = true;
      }
    }
  }
};
</script>
