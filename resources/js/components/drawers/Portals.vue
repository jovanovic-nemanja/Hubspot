<template>
  <div>
    <alert :options.sync="options" :show.sync="showAlert" @close="closeAlert" />
    <div class="action-panel-content">
      <div class="action-container">
        <div class="action-heading">
          <h2>Portals</h2>
          <form @submit.prevent="search" class="form-inline action-panel-search">
            <input
              id="agency-search"
              v-on:keyup="search"
              v-model="keyword"
              class="form-control"
              type="search"
              placeholder="Search"
              aria-label="Search"
              data-item="module-search"
            />
          </form>
        </div>
        <table id="agency-table" class="table table-striped dec">
          <thead>
            <tr>
              <th :class="nameOrder" scope="col" @click="orderBy('name', nameOrderVal)">Portal Name</th>
              <th scope="col">Agency</th>
              <th scope="col">Modules</th>
              <th scope="col">Templates</th>
              <th scope="col">Pages</th>
              <th v-if="isAdmin" class="action" scope="col">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="portal in portals" :key="portal.id">
              <td class="agency-name">{{ portal.name }}</td>
              <td class="agency-name" v-if="portal.agency !== null">{{ portal.agency.name }}</td>
              <td class="agency-name" v-if="portal.agency === null">No Agency</td>
              <td>
                <button @click="showPortalItem('Modules', portal.id)">View Modules</button>
              </td>
              <td>
                <button @click="showPortalItem('Templates', portal.id)">View Templates</button>
              </td>
              <td>
                <button @click="showPortalItem('Pages', portal.id)">View Pages</button>
              </td>
              <td v-if="isAdmin">
                <a class="editor-link" href="#" @click="deletePortal(portal.id)">Delete</a>
              </td>
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
import Alert from "../Alert";

export default {
  name: "Portals",

  computed: mapGetters({
    authUser: "auth/user"
  }),

  components: {
    Alert
  },

  data: () => ({
    appUrl: window.config.appUrl,
    isAdmin: false,
    isAgencyAdmin: false,
    isUser: false,
    itemName: "",
    portals: [],
    portalItems: [],
    plans: [],
    options: {},
    showAlert: false,
    keyword: "",
    nameOrder: {
      desc: true,
      asc: false
    },
    nameOrderVal: "desc",

    form: new Form({
      name: "",
      admin_name: "",
      admin_email: "",
      status: "",
      plan_id: "",
      cover: ""
    })
  }),

  methods: {
    showPortalItem(itemName, portalId) {
      var location = "";

      if (itemName === "Modules") {
        location = this.appUrl + "/api/v1/portals/" + portalId + "/modules";
      } else if (itemName === "Pages") {
        location = this.appUrl + "/api/v1/portals/" + portalId + "/pages";
      } else if (itemName === "Templates") {
        location = this.appUrl + "/api/v1/portals/" + portalId + "/templates";
      }

      window.open(location, "_blank");
    },

    async getPortals(params) {
      const response = await axios.get("/api/v1/portals", { params });
      this.portals = response.data.data;
    },

    deletePortal(id) {
      this.$swal({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "No, cancel!",
        reverseButtons: true
      }).then(result => {
        if (result.value) {
          axios
            .delete("/api/v1/portals/" + id)
            .then(() => {
              this.getPortals({ include: "agency" });

              this.$swal("Deleted!", "The portal has been deleted.", "success");
            })
            .catch(err => {
              this.showAlert = true;
              this.options.content = err.response.data.message;
              this.options.type = "danger";
            });
        } else if (result.dismiss === "cancel") {
          this.$swal("Cancelled", "The portal is safe :)", "error");
        }
      });
    },

    async search() {
      if (this.keyword.length > 1) {
        const response = await axios.get(
          "/api/v1/portals/search/" + this.keyword + "?include=plans"
        );
        this.portals = response.data.data;
      } else {
        this.getPortals({ include: "agency" });
      }
    },

    closeAlert() {
      this.options = {};
      this.showAlert = false;
    },

    orderBy(orderBy, orderVal) {
      if (orderBy == "name") {
        if (orderVal == "desc") {
          this.nameOrderVal = "asc";
          this.nameOrder = {
            desc: true,
            asc: false
          };
          this.getPortals({
            include: "agency",
            order_name: "name",
            order_value: orderVal
          });
        } else if (orderVal == "asc") {
          this.nameOrderVal = "desc";
          this.nameOrder = {
            desc: false,
            asc: true
          };
          this.getPortals({
            include: "agency",
            order_name: "name",
            order_value: orderVal
          });
        }
      }
    }
  },

  beforeMount: function() {
    this.isAdmin = this.authUser.roles.name == "admin" ? true : false;
    this.isAgencyAdmin =
      this.authUser.roles.name == "agency-admin" ? true : false;
    this.isUser = this.authUser.roles.name == "user" ? true : false;
  },

  mounted: function() {
    this.getPortals({ include: "agency" });
  }
};
</script>
