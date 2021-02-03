<template>
  <div>
    <alert :options.sync="options" :show.sync="showAlert" @close="closeAlert" />
    <div class="action-panel-content">
      <div class="action-container">
        <div class="action-heading">
          <h2>Modules</h2>
          <form @submit.prevent="search" class="form-inline action-panel-search">
            <input
              id="plans-search"
              v-on:keyup="search"
              v-model="keyword"
              class="form-control"
              type="search"
              placeholder="Search"
              aria-label="Search"
              data-item="plans-search"
            />
          </form>
        </div>
        <table id="module-table" class="table table-striped">
          <thead>
            <tr>
              <th
                class="users-sort"
                :class="nameOrder"
                scope="col"
                @click="orderBy('name', nameOrderVal)"
              >Module Name</th>
              <th class scope="col">Version</th>
              <th class scope="col">HubSpot Version</th>
              <th class="action" scope="col"></th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(module, idx) in modules.group"
              :key="module.modules.custom_module_group.name"
            >
              <td class="module-name-table" scope="row">
                <span
                  style="display: inline-block;"
                  class="fa fa-clone"
                  aria-hidden="true"
                  title="Module Group"
                ></span>
                {{ module.modules.custom_module_group.name }}
              </td>
              <td></td>
              <td></td>
              <td>
                <button
                  v-bind:disabled="(module.installed || isSuspend) || (!isEligible && !module.modules.is_free)"
                  class="btn-sm"
                  :class="module.installed ? 'btn-success' : 'btn-primary'"
                  @click="installGroupModule(module, idx)"
                >{{ module.button_state }}</button>
              </td>
            </tr>
            <tr v-for="(module, idx) in modules.all" :key="module.modules.custom_module.name">
              <td class="module-name-table" scope="row">
                <span
                  v-if="!isEligible && !module.modules.is_free"
                  style="display: inline-block;"
                  class="fa fa-lock"
                  aria-hidden="true"
                  title="Module Group"
                ></span>
                {{ module.modules.custom_module.name }}
              </td>
              <td>{{ module.modules.custom_module.tags[0].replace('ver-', '') }}</td>
              <td>{{ module.installed_version }}</td>
              <td>
                <label class="global">
                  <input
                    type="checkbox"
                    name="Global"
                    v-model="module.modules.custom_module.global"
                    v-bind:disabled="(module.installed || isSuspend) || (!isEligible && !module.modules.is_free)"
                  />
                  Global
                </label>
                <button
                  v-if="!module.upgradeable && !module.reinstall"
                  v-bind:disabled="(module.installed || isSuspend) || (!isEligible && !module.modules.is_free)"
                  class="btn-sm"
                  :class="module.installed ? 'btn-success' : 'btn-primary'"
                  @click="installModule(module, idx)"
                >{{ module.button_state }}</button>
                <button
                  v-if="module.upgradeable && module.upgradeable_type =='major'"
                  v-bind:disabled="(isSuspend) || (!isEligible && !module.modules.is_free)"
                  class="btn-sm"
                  :class="!module.upgradeable ? 'btn-primary' : 'btn-success'"
                  @click="confirmUpgradeModule(module, idx)"
                >{{ module.button_state }}</button>
                <button
                  v-if="module.upgradeable && module.upgradeable_type =='minor'"
                  v-bind:disabled="(isSuspend) || (!isEligible && !module.modules.is_free)"
                  class="btn-sm"
                  :class="!module.upgradeable ? 'btn-primary' : 'btn-success'"
                  @click="confirmUpgradeModule(module, idx)"
                >{{ module.button_state }}</button>
                <button
                  v-if="module.reinstall"
                  v-bind:disabled="(isSuspend) || (!isEligible && !module.modules.is_free)"
                  class="btn-sm"
                  :class="module.reinstall ? 'btn-primary' : 'btn-success'"
                  @click="confirmUpgradeModule(module, idx)"
                >{{ module.button_state }}</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<script>
import axios from "axios";
import { mapGetters } from "vuex";
import Alert from "../Alert";

export default {
  name: "Modules",

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
    showAlert: false,
    keyword: "",
    nameOrder: {
      desc: true,
      asc: false
    },
    nameOrderVal: "desc",
    isEligible: false
  }),

  methods: {
    getModules(params) {
      if (this.$route.params.id !== undefined) {
        axios
          .get("/api/v1/modules/" + this.$route.params.id, { params })
          .then(res => {
            this.modules = res.data;

            for (let index = 0; index < this.modules.group.length; index++) {
              if (this.modules.group[index].installed) {
                if (this.modules.group[index].upgradeable && this.modules.group[index].upgradeable_type == 'major') {
                  this.modules.group[index].button_state = "Upgrade Major Version";
                } else if (this.modules.group[index].upgradeable && this.modules.group[index].upgradeable_type == 'minor') {
                  this.modules.group[index].button_state = "Upgrade Module";
                } else {
                  this.modules.group[index].button_state = "Installed";
                }
              } else {
                this.modules.group[index].button_state = "Install Module";
              }
            }

            for (let index = 0; index < this.modules.all.length; index++) {
              if (this.modules.all[index].globalModuleId) {
                this.modules.all[index].modules.custom_module.global = true;
              }
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

    installModule(module, idx) {
      this.closeAlert();
      this.modules.all[idx].button_state = "Installing...";
      const moduleData = {
        portal_id: this.$route.params.id,
        module: module.modules
      };

      axios
        .post("/api/v1/modules/install", moduleData)
        .then(res => {
          this.options.content =
            moduleData.module.custom_module.name + " has been installed";
          this.options.type = "success";
          this.showAlert = true;

          this.modules.all[idx].button_state = "Installed";
          this.modules.all[idx].installed = true;
          this.modules.all[idx].installed_version = res.data.tags[0].replace('ver-', '');
        })
        .catch(err => {
          this.modules.all[idx].button_state = "Install Module";
          this.showAlert = true;
          this.options.content = err.response.data.message;
          this.options.type = "danger";
        });
    },

    confirmUpgradeModule(module, idx) {
      if (module.upgradeable_type === 'major') {
        this.$swal({
          title: "Are you sure?",
          text: "Major Version upgrades introduce new features that may not be backwards compatible and can break your website.",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Upgrade Module",
          cancelButtonText: "Cancel",
          reverseButtons: true
        }).then(result => {
          if (result.value) {
            this.upgradeModule(module, idx);
          }
        })
      } else {
        this.upgradeModule(module, idx);
      }
    },

    upgradeModule(module, idx) {      
      this.closeAlert();
      this.modules.all[idx].button_state = "Installing...";
      const moduleData = {
        portal_id: this.$route.params.id,
        module: module.modules,
        moduleId: module.moduleId,
        globalModuleId: module.globalModuleId ? module.globalModuleId : null
      };

      axios
        .post("/api/v1/modules/upgrade", moduleData)
        .then(res => {
          this.options.content =
            moduleData.module.custom_module.name + " has been installed";
          this.options.type = "success";
          this.showAlert = true;

          this.modules.all[idx].button_state = "Installed";
          this.modules.all[idx].upgradeable = false;
          this.modules.all[idx].reinstall = false;
          this.modules.all[idx].installed = true;
          this.modules.all[idx].installed_version = res.data.tags[0].replace('ver-', '');
        })
        .catch(err => {
          this.modules.all[idx].button_state = "Install Module";
          this.showAlert = true;
          this.options.content = err.response.data.message;
          this.options.type = "danger";
        });
    },

    installGroupModule(module, idx) {
      this.closeAlert();
      this.modules.group[idx].button_state = "Installing...";
      var installStatus = "";
      var installMessage = "";

      var moduleData = {
        portal_id: this.$route.params.id,
        module: module.modules
      };

      axios
        .post("/api/v1/modules/install?mode=group", moduleData)
        .then(res => {
          for (
            let index = 0;
            index < res.data.install_results.length;
            index++
          ) {
            if (res.data.install_results[index].status == false) {
              installStatus = "warning";
              installMessage =
                moduleData.module.custom_module_group.name +
                " has been installed" +
                " with error, Module " +
                res.data.install_results[index].name +
                " not found.";
              break;
            }
          }

          if (installStatus === "warning") {
            this.options.content = installMessage;
            this.options.type = "warning";
            this.modules.group[idx].button_state = "Install Module";
            this.modules.group[idx].installed = false;
          } else {
            this.options.content =
              moduleData.module.custom_module_group.name +
              " has been installed";
            this.options.type = "success";
            this.modules.group[idx].button_state = "Installed";
            this.modules.group[idx].installed = true;
          }

          this.modules = res.data.data;

          for (let index = 0; index < this.modules.all.length; index++) {
            if (this.modules.all[index].installed) {
              this.modules.all[index].button_state = "Installed";
            } else {
              this.modules.all[index].button_state = "Install Module";
            }
          }

          for (let index = 0; index < this.modules.group.length; index++) {
            if (this.modules.group[index].installed) {
              this.modules.group[index].button_state = "Installed";
            } else {
              this.modules.group[index].button_state = "Install Module";
            }
          }

          this.showAlert = true;
        })
        .catch(err => {
          this.showAlert = true;
          this.modules.group[idx].button_state = "Install Module";
          this.options.content = err.response.data.message;
          this.options.type = "danger";
        });
    },

    orderBy(orderBy, orderVal) {
      if (orderBy == "name") {
        if (orderVal == "desc") {
          this.nameOrderVal = "asc";
          this.nameOrder = {
            desc: true,
            asc: false
          };
          this.getModules({
            order_name: "name",
            order_value: orderVal
          });
        } else if (orderVal == "asc") {
          this.nameOrderVal = "desc";
          this.nameOrder = {
            desc: false,
            asc: true
          };
          this.getModules({
            order_name: "name",
            order_value: orderVal
          });
        }
      }
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
