<template>
  <div class="container-fluid">
    <div class="row flex-nowrap">
      <sidebar-disabled />

      <!-- Portals -->
      <div class="right-panel">
        <div class="inner-container">
          <div class="row">
            <div class="col-md-8">
              <h1>{{portal.name}} updated</h1>
            </div>
            <div class="col-md-4 action">
              <button @click="openPortalDetail(portal.id)" class="btn btn-primary">Finish</button>
            </div>
          </div>
          <div class="main-container installer">
            <div class="col-md-12 table-column">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th scope="col">Filename</th>
                    <th scope="col"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="installerResult in installerResults.templates"
                    :key="installerResult.path"
                  >
                    <th scope="row">{{ installerResult.path }}</th>
                    <td>
                      <img src="/images/icon-check.svg" />
                    </td>
                  </tr>
                  <tr
                    v-for="installerResult in installerResults.customModules"
                    :key="installerResult.path"
                  >
                    <th scope="row">{{ installerResult.path }}</th>
                    <td>
                      <img src="/images/icon-check.svg" />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SidebarDisabled from "~/components/SidebarDisabled";
import axios from "axios";

export default {
  middleware: "auth",

  components: {
    SidebarDisabled
  },

  data: () => ({
    installerResults: [],
    portal: JSON.parse(localStorage.portalData)
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
    }
    
  },

  beforeMount: function() {
    this.installerResults = JSON.parse(localStorage.installerResult);
  }
};
</script>