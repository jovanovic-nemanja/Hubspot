<template>
  <div class="container-fluid">
    <div class="row flex-nowrap">
      <SidebarDisabled />
      <!-- Portals -->
      <div class="right-panel">
        <alert :options.sync="options" :show.sync="showAlert" @close="closeAlert" />
        <div class="inner-container">
          <div class="row">
            <div class="col-md-12">
              <div class="setup-message">
                <h1>{{ pageInformation.name }} has been added!</h1>

                <p>
                  All modules have made it to your portal and your new page is
                  waiting for you here:
                  <br />
                  <a
                    class="btn btn-primary"
                    v-bind:href="
                      'https://app.hubspot.com/content/' +
                        pageInformation.portal.hub_id +
                        '/edit/' +
                        pageInformation.page_id +
                        '/content'
                    "
                    target="_blank"
                    rel="noopener noreferrer"
                  >Edit Page</a>
                </p>

                <!-- <p>List modules:</p>
                <ul>
                  <li
                    v-for="module in pageInformation.modules"
                    :key="module.body.widget_name"
                  >{{ module.body.widget_name }}</li>
                </ul>-->
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
import SidebarDisabled from "~/components/SidebarDisabled";
import Alert from "../components/Alert";

export default {
  middleware: "auth",

  computed: mapGetters({
    authUser: "auth/user"
  }),

  components: {
    SidebarDisabled,
    Alert
  },

  data: () => ({
    pageInformation: {},
    options: {},
    showAlert: false
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

    this.pageInformation = JSON.parse(localStorage.customPageData);

    this.showAlert = true;
    this.options.content = this.pageInformation.name + " has been added!";
    this.options.type = "success";
  }
};
</script>
