<template>
  <div>
    <alert :options.sync="options" :show.sync="showAlert" @close="closeAlert" />
    <div class="action-panel-content account-details">
      <div class="action-container">
        <div class="action-heading">
          <h2>Module Audit</h2>
        </div>
        <table id="plan-table" class="table table-striped asc">
          <thead>
            <tr>
              <th scope="col">Module Name</th>
              <th scope="col">Total Install</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="module in moduleAudit" :key="module.id">
              <td class="plan-name">{{ module.name }}</td>
              <td>{{ module.total_install }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
import Form from "vform";
import axios from "axios";
import JQuery from "jquery";
import Alert from "../Alert";

export default {
  name: "ModuleAudit",

  computed: mapGetters({
    authUser: "auth/user"
  }),

  components: {
    Alert
  },

  data: () => ({
    isAdmin: false,
    isAgencyAdmin: false,
    isUser: false,
    moduleAudit: [],
    options: {},
    showAlert: false,
    isLoading: false
  }),

  methods: {
    async getModuleAudit() {
      try {
        const response = await axios.get("/api/v1/modules/audit");
        this.moduleAudit = response.data;
      } catch (err) {
        this.showAlert = true;
        this.options.content = err.response.data.message;
        this.options.type = "danger";
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
    this.isCustomLogo =
      this.authUser.agencies.plans.custom_logo == "yes" ? true : false;
  },

  mounted: function() {
    this.getModuleAudit();
  }
};
</script>
