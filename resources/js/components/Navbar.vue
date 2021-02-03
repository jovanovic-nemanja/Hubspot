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

let $ = JQuery;

export default {
  components: {
    AccountDetails,
    Agencies,
    Portals,
    Billings,
    Plans,
    Users,
    ModuleAudit
  },

  data: () => ({
    isAdmin: false,
    isAgencyAdmin: false,
    isUser: false,
    menu: "",
    logo: ""
  }),

  computed: mapGetters({
    authUser: "auth/user"
  }),

  methods: {
    openMenu(panel) {
      this.$refs.accountDrawer.close();
      this.$refs.agencyDrawer.close();
      this.$refs.portalDrawer.close();
      this.$refs.billingDrawer.close();
      this.$refs.planDrawer.close();
      this.$refs.userDrawer.close();
      this.$refs.moduleAuditDrawer.close();

      if (panel === "action-detail") {
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
    }
  },

  beforeMount: function() {
    this.isAdmin = this.authUser.roles.name == "admin" ? true : false;
    this.isAgencyAdmin =
      this.authUser.roles.name == "agency-admin" ? true : false;
    this.isUser = this.authUser.roles.name == "user" ? true : false;

    if (
      this.authUser.agencies.plans.custom_logo == "yes" &&
      this.authUser.agencies.cover
    ) {
      this.logo = this.authUser.agencies.cover;
    } else {
      this.logo = "/images/icon-sr-white.svg";
    }
  },

  mounted: function() {
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
</script>
