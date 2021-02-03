<template>
  <div class="container-fluid">
    <div class="row flex-nowrap">
      <sidebar-disabled />

      <!-- Portals -->
      <div class="right-panel">
        <alert :options.sync="options" :show.sync="showAlert" @close="closeAlert" />
        <div class="inner-container">
          <div class="row">
            <div class="col-md-12">
              <h1>Update {{portal.name}}</h1>
            </div>
          </div>
          <div class="main-container">
            <div class="col-md-6">
              <div class="setup-message text-left">
                <h4>
                  Update Sprocket Rocket
                </h4>
                <p>
                  <strong>Any changes to the following files will be overwritten</strong>
                </p>
                <ul class="small">
                  <li>/sr/theme.json</li>
                  <li>/sr/fields.json</li>
                  <li>/sr/templates/variables.html</li>
                  <li>/sr/templates/header.html</li>
                  <li>/sr/templates/footer-includes.html</li>
                  <li>/sr/templates/page.html</li>
                  <li>/sr/templates/dnd_page.html</li>
                  <li>/sr/templates/modules.html</li>
                  <li>/sr/css/sr-base.css</li>
                  <li>/sr/css/bootstrap.css</li>
                  <li>/sr/js/bootstrap.js</li>
                  <li>/sr/js/interaction.js</li>
                  <li>/sr/js/jquery.js</li>
                </ul>
                <button
                  :disabled="loading"
                  @click="runUpdateWizard"
                  class="btn btn-primary"
                >{{ buttonName }}</button>
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
import Alert from "../components/Alert";

export default {
  middleware: "auth",

  components: {
    SidebarDisabled,
    Alert
  },

  data: () => ({
    buttonName: "Update",
    loading: false,
    options: {},
    showAlert: false,
    portal: JSON.parse(localStorage.portalData)
  }),

  beforeMount: function() {
    this.$store.dispatch("auth/fetchUser");
  },

  metaInfo() {
    return { title: this.$t("home") };
  },

  methods: {
    runUpdateWizard() {
      const self = this;
      self.loading = true;
      self.buttonName = "Updating";

      let portalData = JSON.parse(localStorage.portalData);

      axios
        .post("/api/v1/portals/update-wizard", portalData)
        .then(function(res) {
          self.loading = false;
          self.buttonName = "Update";

          localStorage.installerResult = JSON.stringify(res.data.update_result);
          self.$router.push({ name: "sr-update-installer" });
        })
        .catch(function(err) {
          self.loading = false;
          self.buttonName = "Update";
          self.showAlert = true;
          self.options.content = err.response.data.message;
          self.options.type = "danger";
        });
    },

    closeAlert() {
      this.options = {};
      this.showAlert = false;
    }
  }
};
</script>
