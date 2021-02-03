<template>
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
          <div v-for="portal in portals" :key="portal.id">
            <div class="portal-link" @click="openPortalDetail(portal.id)">
              <div class="portal-info-card">
                <p>
                  {{ portal.name }}
                  <span
                    v-if="!portal.is_lock"
                    style="display: inline-block;"
                    class="fa fa-lock"
                    aria-hidden="true"
                  ></span>
                </p>
                <span class="portal-id">{{ portal.hub_id }} {{ portal.hub_url }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
import axios from "axios";
import SidebarDisabled from "~/components/SidebarDisabled";
import LoginWithHubspot from "~/components/LoginWithHubspot";
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
    Alert
  },

  data: () => ({
    appUrl: window.config.appUrl,
    portals: [],
    options: {},
    showAlert: false,
    portalLimitExceeded: false
  }),

  metaInfo() {
    return { title: this.$t("home") };
  },

  methods: {
    async getPortals() {
      const response = await axios.get("/api/v1/portals");

      this.portals = response.data.data;
    },

    async openPortalDetail(portalId) {
      const response = await axios.get("/api/v1/portals/access/" + portalId);

      if (response.data.status) {
        this.$router.push({ path: `/portals-main/${portalId}` });
      } else {
        const newWindow = openWindow("", this.$t("login"));

        const url = await this.$store.dispatch("auth/fetchOauthUrl", {
          provider: "hubspot"
        });

        newWindow.location.href = url;
      }
    },

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
    this.getPortals();

    this.$root.$on("loadPortals", () => {
      this.getPortals();

      this.portalLimitExceeded =
        this.authUser.agencies.plans.portal_limit <=
        this.authUser.agencies.portals_count
          ? true
          : false;
    });

    this.$root.$on("reloadPage", () => {
      window.location.reload();
    });

    this.$root.$on("addPortalsError", err => {
      this.showAlert = true;
      this.options.content = err.response.data.message;
      this.options.type = "danger";

      this.portalLimitExceeded =
        this.authUser.agencies.plans.portal_limit <=
        this.authUser.agencies.portals_count
          ? true
          : false;
    });
  }
};

/**
 * @param  {Object} options
 * @return {Window}
 */
function openWindow(url, title, options = {}) {
  if (typeof url === "object") {
    options = url;
    url = "";
  }

  options = { url, title, width: 600, height: 720, ...options };

  const dualScreenLeft =
    window.screenLeft !== undefined ? window.screenLeft : window.screen.left;
  const dualScreenTop =
    window.screenTop !== undefined ? window.screenTop : window.screen.top;
  const width =
    window.innerWidth ||
    document.documentElement.clientWidth ||
    window.screen.width;
  const height =
    window.innerHeight ||
    document.documentElement.clientHeight ||
    window.screen.height;

  options.left = width / 2 - options.width / 2 + dualScreenLeft;
  options.top = height / 2 - options.height / 2 + dualScreenTop;

  const optionsStr = Object.keys(options)
    .reduce((acc, key) => {
      acc.push(`${key}=${options[key]}`);
      return acc;
    }, [])
    .join(",");

  const newWindow = window.open(url, title, optionsStr);

  if (window.focus) {
    newWindow.focus();
  }

  return newWindow;
}
</script>
