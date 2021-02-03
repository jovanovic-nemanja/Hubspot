<template>
  <div class="container-fluid">
    <div class="row flex-nowrap">
      <sidebar-disabled />

      <!-- Portals -->
      <div class="right-panel">
        <div class="inner-container">
          <div class="row">
            <div class="col-md-8">
              <h1>Setup Sprocket Rocket</h1>
            </div>
            <div class="col-md-4 action">
              <router-link :to="{ path: 'portals-main' }">
                <button class="btn btn-primary">Next</button>
              </router-link>
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
                    v-for="installerResult in installerResults.customModules"
                    :key="installerResult.path"
                  >
                    <th scope="row">{{ installerResult.path }}</th>
                    <td v-if="installerResult.result.status === 'error'">
                      <img v-tooltip="installerResult.result.message" src="/images/icon-cross.svg" />
                    </td>
                    <td v-else>
                      <img src="/images/icon-check.svg" />
                    </td>
                  </tr>
                  <tr
                    v-for="installerResult in installerResults.templates"
                    :key="installerResult.path"
                  >
                    <th scope="row">{{ installerResult.path }}</th>
                    <td v-if="installerResult.result.status === 'error'">
                      <img v-tooltip="installerResult.result.message" src="/images/icon-cross.svg" />
                    </td>
                    <td v-else>
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

export default {
  middleware: "auth",

  components: {
    SidebarDisabled
  },

  data: () => ({
    installerResults: []
  }),

  metaInfo() {
    return { title: this.$t("home") };
  },

  beforeMount: function() {
    this.installerResults = JSON.parse(localStorage.installerResult);
  }
};
</script>

<style>
  .tooltip {
    display: block !important;
    z-index: 100000;
  }

  .tooltip .tooltip-inner {
    background: black;
    color: white;
    border-radius: 16px;
    padding: 5px 10px 4px;
  }

  .tooltip .tooltip-arrow {
    width: 0;
    height: 0;
    border-style: solid;
    position: absolute;
    margin: 5px;
    border-color: black;
    z-index: 1;
  }

  .tooltip[x-placement^="top"] {
    margin-bottom: 5px;
  }

  .tooltip[x-placement^="top"] .tooltip-arrow {
    border-width: 5px 5px 0 5px;
    border-left-color: transparent !important;
    border-right-color: transparent !important;
    border-bottom-color: transparent !important;
    bottom: -5px;
    left: calc(50% - 5px);
    margin-top: 0;
    margin-bottom: 0;
  }

  .tooltip[x-placement^="bottom"] {
    margin-top: 5px;
  }

  .tooltip[x-placement^="bottom"] .tooltip-arrow {
    border-width: 0 5px 5px 5px;
    border-left-color: transparent !important;
    border-right-color: transparent !important;
    border-top-color: transparent !important;
    top: -5px;
    left: calc(50% - 5px);
    margin-top: 0;
    margin-bottom: 0;
  }

  .tooltip[x-placement^="right"] {
    margin-left: 5px;
  }

  .tooltip[x-placement^="right"] .tooltip-arrow {
    border-width: 5px 5px 5px 0;
    border-left-color: transparent !important;
    border-top-color: transparent !important;
    border-bottom-color: transparent !important;
    left: -5px;
    top: calc(50% - 5px);
    margin-left: 0;
    margin-right: 0;
  }

  .tooltip[x-placement^="left"] {
    margin-right: 5px;
  }

  .tooltip[x-placement^="left"] .tooltip-arrow {
    border-width: 5px 0 5px 5px;
    border-top-color: transparent !important;
    border-right-color: transparent !important;
    border-bottom-color: transparent !important;
    right: -5px;
    top: calc(50% - 5px);
    margin-left: 0;
    margin-right: 0;
  }

  .tooltip.popover .popover-inner {
    background: #f9f9f9;
    color: black;
    padding: 24px;
    border-radius: 5px;
    box-shadow: 0 5px 30px rgba(black, .1);
  }

  .tooltip.popover .popover-arrow {
    border-color: #f9f9f9;
  }

  .tooltip[aria-hidden='true'] {
    visibility: hidden;
    opacity: 0;
    transition: opacity .15s, visibility .15s;
  }

  .tooltip[aria-hidden='false'] {
    visibility: visible;
    opacity: 1;
    transition: opacity .15s;
  }
</style>