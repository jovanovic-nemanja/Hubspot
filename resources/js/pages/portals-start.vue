<template>
  <div>
    <div class="container-fluid">
      <div class="row flex-nowrap">
        <sidebar-disabled />

        <!-- Portals -->
        <div class="right-panel">
          <alert :options.sync="options" :show.sync="showAlert" @close="closeAlert" />
          <div class="inner-container">
            <div class="row">
              <div class="col-md-8">
                <h1>Portals</h1>
              </div>
              <div class="col-md-4 action">
                <login-with-hubspot :disabled="portalLimitExceeded || isSuspend" />
              </div>
            </div>
            <div class="main-container">
              <div class="col-md-6">
                <div class="setup-message">
                  <img src="/images/icon-portals.svg" alt="Add your HubSpot Portal" />
                  <p>First, you need to Add a Portal</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- <start-up /> -->
  </div>
</template>

<script>
import { mapGetters } from "vuex";
import axios from "axios";
import SidebarDisabled from "~/components/SidebarDisabled";
import LoginWithHubspot from "~/components/LoginWithHubspot";
// import StartUp from "~/components/modals/StartUp";
import Alert from "../components/Alert";

export default {
  middleware: "auth",

  computed: mapGetters({
    authUser: "auth/user",
    billingStatus: "billing/status"
  }),

  components: {
    SidebarDisabled,
    LoginWithHubspot,
    // StartUp,
    Alert
  },

  data: () => ({
    options: {},
    showAlert: false,
    portalLimitExceeded: false
  }),

  metaInfo() {
    return { title: this.$t("home") };
  },

  methods: {
    closeAlert() {
      this.options = {};
      this.showAlert = false;
    }
  },

  beforeMount: function() {
    this.isAdmin = this.authUser.roles.name == "admin" ? true : false;
    this.isAgencyAdmin =
      this.authUser.roles.name == "agency-admin" ? true : false;
    this.isUser = this.authUser.roles.name == "user" ? true : false;
    this.isSuspend = this.authUser.agencies.status == "suspend" ? true : false;
    this.portalLimitExceeded =
      this.authUser.agencies.plans.portal_limit <=
      this.authUser.agencies.portals_count
        ? true
        : false;

    if (this.isAgencyAdmin && this.billingStatus == "suspend") {
      this.isSuspend = true;

      this.showAlert = true;
      this.options.content =
        "Please update your Billing Information to continue using Sprocket Rocket.";
      this.options.type = "warning";
    } else if (this.isUser && this.billingStatus == "suspend") {
      this.isSuspend = true;

      this.showAlert = true;
      this.options.content =
        "Please ask your agency admin to update their Billing Information to continue using Sprocket Rocket.";
      this.options.type = "warning";
    }
  },

  mounted: function() {
    if (!this.isAdmin) {
      this.$modal.show("welcome");
    }
  }
};
</script>
