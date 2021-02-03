<template>
  <div>
    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light justify-content-between main-navigation">
      <router-link class="navbar-brand" to="/">
        <img v-bind:src="logo" alt="Sprocket Rocket" style="height: 55px;" />
      </router-link>
      <button
        class="navbar-toggler"
        type="button"
        data-toggle="collapse"
        data-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent"
        aria-expanded="false"
        aria-label="Toggle navigation"
      >
        <span class="navbar-toggler-icon" />
      </button>
      <div id="navbarSupportedContent" class="collapse navbar-collapse">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a id="modules" class="nav-link" @click.prevent="openMenu('module')" href="#">
              <span v-if="moduleInformation.information.is_upgrade_available"
              style="display: inline-block; color: red;"
              aria-hidden="true"
            >•</span>
            Modules</a>
          </li>
          <li class="nav-item">
            <a id="layouts" class="nav-link" @click.prevent="openMenu('layouts')" href="#">
            Layouts</a>
          </li>
          <!-- <li class="nav-item">
            <a id="history" class="nav-link" @click.prevent="openMenu('history')" href="#">
            History</a>
          </li> -->
          <li class="nav-item">
            <a
              id="website"
              class="nav-link"
              @click.prevent="openMenu('website-page')"
              href="#"
            >Website Pages</a>
          </li>
          <li class="nav-item">
            <a
              id="landing"
              class="nav-link"
              @click.prevent="openMenu('landing-page')"
              href="#"
            >Landing Pages</a>
          </li>
          <li class="nav-item">
            <a
              id="system"
              class="nav-link"
              @click.prevent="openMenu('system-page')"
              href="#"
            >System Templates</a>
          </li>
          <li class="nav-item">
            <a id="blog" class="nav-link" @click.prevent="openMenu('blog')" href="#">Blogs</a>
          </li>
          <li class="nav-item dropdown">
            <a
              class="nav-link dropdown-toggle"
              href="#"
              id="navbarDropdown"
              role="button"
              data-toggle="dropdown"
              aria-haspopup="true"
              aria-expanded="false"
            >{{ portal.name }}</a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
              <a
                class="dropdown-item portal-link"
                href="#"
                @click="openPortalDetail(portal.id)"
                v-for="portal in portals"
                :key="portal.id"
              >
                <span class="client-name">
                  {{ portal.name }}
                  <span
                    v-if="!portal.is_lock"
                    style="display: inline-block;"
                    class="fa fa-lock"
                    aria-hidden="true"
                  ></span>
                </span>
                <span class="portal-id">{{ portal.hub_id }} {{ portal.hub_url }}</span>
              </a>
              <!-- <router-link
                v-for="portal in portals"
                class="dropdown-item"
                :key="portal.id"
                :to="{
                  name: 'portals-detail',
                  params: { id: portal.id }
                }"
              >
                <span class="client-name">
                  {{ portal.name }}
                  <span v-if="!portal.is_lock" style="display: inline-block;" class="fa fa-lock" aria-hidden="true"></span>
                </span>
                <span class="portal-id">{{ portal.hub_id }} {{ portal.hub_url }}</span>
              </router-link>-->
              <login-with-hubspot :disabled="portalLimitExceeded || isSuspend" />
            </div>
          </li>
          <li class="nav-item dropdown">
            <a
              id="navbarDropdown"
              class="nav-link dropdown-toggle"
              href="#"
              role="button"
              data-toggle="dropdown"
              aria-haspopup="true"
              aria-expanded="false"
            >
              <img src="/images/icon-user.svg" />
            </a>
            <div class="dropdown-menu user-menu" aria-labelledby="navbarDropdown">
              <a
                v-if="isAgencyAdmin || isUser"
                id="account"
                @click.prevent="openMenu('action-detail')"
                class="dropdown-item client-name"
                href="#"
              >Account Details</a>
              <a
                v-if="isAdmin"
                id="agencies"
                @click.prevent="openMenu('agency')"
                class="dropdown-item client-name"
                href="#"
              >Agencies</a>
              <a
                v-if="isAdmin"
                id="portals"
                @click.prevent="openMenu('portal')"
                class="dropdown-item client-name"
                href="#"
              >Portals</a>
              <a
                v-if="isAgencyAdmin"
                id="billing"
                @click.prevent="openMenu('billing')"
                class="dropdown-item client-name"
                href="#"
              >Billing Information</a>
              <a
                v-if="isAdmin"
                id="plans"
                @click.prevent="openMenu('plan')"
                class="dropdown-item client-name"
                href="#"
              >Plans</a>
              <a
                v-if="isAgencyAdmin || isAdmin"
                id="users"
                @click.prevent="openMenu('user')"
                class="dropdown-item client-name"
                href="#"
              >Users</a>
              <a
                v-if="isAdmin"
                id="module-audit"
                @click.prevent="openMenu('module-audit')"
                class="dropdown-item client-name"
                href="#"
              >Module Audit</a>
              <a @click.prevent="logout()" class="dropdown-item client-name" href="#">Logout</a>
            </div>
          </li>
        </ul>
      </div>
    </nav>

    <Drawer :direction="'right'" :exist="true" ref="moduleDrawer">
      <modules v-if="menu == 'module'" />
    </Drawer>
    <Drawer :direction="'right'" :exist="true" ref="layoutDrawer">
      <layouts v-if="menu == 'layouts'" :updated="layoutUpdated" @reset="resetLayoutUpdatedState"/>
    </Drawer>
    <Drawer :direction="'right'" :exist="true" ref="historyDrawer">
      <history v-if="menu == 'history'" :updated="historyUpdated" @reset="resetHistoryUpdatedState"/>
    </Drawer>
    <Drawer :direction="'right'" :exist="true" ref="websitePagesDrawer">
      <website-pages v-if="menu == 'website-page'" />
    </Drawer>
    <Drawer :direction="'right'" :exist="true" ref="landingPagesDrawer">
      <landing-pages v-if="menu == 'landing-page'" />
    </Drawer>
    <Drawer :direction="'right'" :exist="true" ref="systemPagesDrawer">
      <system-pages v-if="menu == 'system-page'" />
    </Drawer>
    <Drawer :direction="'right'" :exist="true" ref="blogsDrawer">
      <blogs v-if="menu == 'blog'" />
    </Drawer>

    <Drawer :direction="'right'" :exist="true" ref="accountDrawer">
      <account-details v-if="menu == 'action-detail'" />
    </Drawer>
    <Drawer :direction="'right'" :exist="true" ref="agencyDrawer">
      <agencies v-if="menu == 'agency'" />
    </Drawer>
    <Drawer :direction="'right'" :exist="true" ref="portalDrawer">
      <portals v-if="menu == 'portal'" />
    </Drawer>
    <Drawer :direction="'right'" :exist="true" ref="billingDrawer">
      <billings v-if="menu == 'billing'" />
    </Drawer>
    <Drawer :direction="'right'" :exist="true" ref="planDrawer">
      <plans v-if="menu == 'plan'" />
    </Drawer>
    <Drawer :direction="'right'" :exist="true" ref="userDrawer">
      <users v-if="menu == 'user'" />
    </Drawer>
    <Drawer :direction="'right'" :exist="true" ref="moduleAuditDrawer">
      <module-audit v-if="menu == 'module-audit'" />
    </Drawer>
  </div>
</template>

<script>
import axios from "axios";
import { mapGetters } from "vuex";
import JQuery from "jquery";
import AccountDetails from "./drawers/AccountDetails";
import Agencies from "./drawers/Agencies";
import Portals from "./drawers/Portals";
import Billings from "./drawers/Billings";
import Plans from "./drawers/Plans";
import Users from "./drawers/Users";
import ModuleAudit from "./drawers/ModuleAudit";
import Blogs from "./drawers/Blogs";
import Modules from "./drawers/Modules";
import Layouts from "./drawers/Layouts";
import History from "./drawers/History";
import LandingPages from "./drawers/LandingPages";
import WebsitePages from "./drawers/WebsitePages";
import SystemPages from "./drawers/SystemPages";
import LoginWithHubspot from "~/components/LoginWithHubspot";

let $ = JQuery;

export default {
  components: {
    AccountDetails,
    Agencies,
    Portals,
    Billings,
    Plans,
    Users,
    ModuleAudit,
    Blogs,
    Layouts,
    History,
    Modules,
    LandingPages,
    WebsitePages,
    SystemPages,
    LoginWithHubspot
  },

  data: () => ({
    portals: [],
    portal: {},
    menu: "",
    logo: "",
    portalLimitExceeded: false,
    moduleInformation: {
      information: {
        is_upgrade_available: false
      }
    },
    layoutUpdated:false,
    historyUpdated: false
  }),

  computed: mapGetters({
    authUser: "auth/user",
    billingStatus: "billing/status"
  }),

  methods: {
    resetLayoutUpdatedState(){
      this.layoutUpdated = false;
    },
    resetHistoryUpdatedState(){
      this.historyUpdated = false;
    },
    openMenu(panel) {
      this.$refs.moduleDrawer.close();
      this.$refs.layoutDrawer.close();
      this.$refs.historyDrawer.close();
      this.$refs.websitePagesDrawer.close();
      this.$refs.systemPagesDrawer.close();
      this.$refs.landingPagesDrawer.close();
      this.$refs.blogsDrawer.close();
      this.$refs.accountDrawer.close();
      this.$refs.agencyDrawer.close();
      this.$refs.portalDrawer.close();
      this.$refs.billingDrawer.close();
      this.$refs.planDrawer.close();
      this.$refs.userDrawer.close();
      this.$refs.moduleAuditDrawer.close();

      if (panel === "module") {
        this.menu = "module";
        this.$refs.moduleDrawer.open();
      } else if (panel === "layouts") {
        this.menu = "layouts";
        this.layoutUpdated = true;
        this.$refs.layoutDrawer.open();
      } else if (panel === "history") {
        this.menu = "history";
        this.historyUpdated = true;
        this.$refs.historyDrawer.open();
      } else if (panel === "website-page") {
        this.menu = "website-page";
        this.$refs.websitePagesDrawer.open();
      } else if (panel === "landing-page") {
        this.menu = "landing-page";
        this.$refs.landingPagesDrawer.open();
      } else if (panel === "system-page") {
        this.menu = "system-page";
        this.$refs.systemPagesDrawer.open();
      } else if (panel === "blog") {
        this.menu = "blog";
        this.$refs.blogsDrawer.open();
      } else if (panel === "action-detail") {
        this.menu = "action-detail";
        this.$refs.accountDrawer.open();
      } else if (panel === "agency") {
        this.menu = "agency";
        this.$refs.agencyDrawer.open();
      } else if (panel === "portal") {
        this.menu = "portal";
        this.$refs.portalDrawer.open();
      } else if (panel === "billing") {
        this.menu = "billing";
        this.$refs.billingDrawer.open();
      } else if (panel === "plan") {
        this.menu = "plan";
        this.$refs.planDrawer.open();
      } else if (panel === "user") {
        this.menu = "user";
        this.$refs.userDrawer.open();
      } else if (panel === "module-audit") {
        this.menu = "module-audit";
        this.$refs.moduleAuditDrawer.open();
      }
    },

    async logout() {
      // Log out the user.
      await this.$store.dispatch("auth/logout");

      // Redirect to login.
      this.$router.push({ name: "welcome" });
    },

    async getPortals() {
      const response = await axios.get("/api/v1/portals");
      this.portals = response.data.data;
    },

    async getPortalById(id) {
      const response = await axios.get("/api/v1/portals/" + id);
      this.portal = response.data;
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

    async getModuleInformation(portalId) {
      const response = await axios.get("/api/v1/modules/information/" + portalId);
      this.moduleInformation = response.data;
    }
  },

  watch: {
    $route() {
      if (this.$route.name == "portals-detail") {
        this.getPortalById(this.$route.params.id);
        this.getModuleInformation(this.$route.params.id);
      }
    }
  },

  created: function() {
    this.getPortals();
    this.getPortalById(this.$route.params.id);
    this.getModuleInformation(this.$route.params.id);
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
    } else if (this.isUser && this.billingStatus == "suspend") {
      this.isSuspend = true;
    }

    if (
      this.authUser.agencies.plans.custom_logo == "yes" &&
      this.authUser.agencies.cover
    ) {
      this.logo = this.authUser.agencies.cover;
    } else {
      this.logo = "/images/icon-sr-white.svg";
    }

    const unwatch = this.$watch(
      () => this.$route,
      (route, prevRoute) => {
        this.getPortalById(route.params.id);
        unwatch();
      }
    );
  },

  mounted: function() {
    this.$root.$on("addPortalsError", err => {
      this.portalLimitExceeded =
        this.authUser.agencies.plans.portal_limit <=
        this.authUser.agencies.portals_count
          ? true
          : false;
    });

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

    if ($(window).width() > 990) {
      $(".dropdown")
        .mouseover(function() {
          $(this)
            .addClass("show")
            .attr("aria-expanded", "true");
          $(this)
            .find(".dropdown-menu")
            .addClass("show");
        })
        .mouseout(function() {
          $(this)
            .removeClass("show")
            .attr("aria-expanded", "false");
          $(this)
            .find(".dropdown-menu")
            .removeClass("show");
        });
    }

    $(".main-navigation .navbar-nav a").on("click", function() {
      $(".main-navigation .navbar-nav")
        .find("li.active")
        .removeClass("active");
      $(this)
        .parent("li")
        .addClass("active");
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
