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
              <h1>Setup Sprocket Rocket</h1>
            </div>
          </div>
          <div class="main-container">
            <div class="col-md-6">
              <div class="setup-message">
                <img src="/images/icon-sr-dark.svg" alt="Sprocket Rocket Icon" />
                <p>
                  To use Sprocket Rocket in your portal, first we need to
                  install the starter template and its dependencies.
                </p>
                <button
                  :disabled="loading"
                  @click="runSetupWizard"
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
    buttonName: "Run Installer",
    loading: false,
    options: {},
    showAlert: false
  }),

  beforeMount: function() {
    this.$store.dispatch("auth/fetchUser");
  },

  metaInfo() {
    return { title: this.$t("home") };
  },

  methods: {
    runSetupWizard() {
      const self = this;
      self.loading = true;
      self.buttonName = "Installing";

      let portalData = JSON.parse(localStorage.portalData);

      axios
        .post("/api/v1/portals/setup-wizard", portalData)
        .then(function(res) {
          self.loading = false;
          self.buttonName = "Run Installer";

          localStorage.installerResult = JSON.stringify(res.data.setup_result);
          self.$router.push({ name: "sr-setup-installer" });
        })
        .catch(function(err) {
          self.loading = false;
          self.buttonName = "Run Installer";
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
