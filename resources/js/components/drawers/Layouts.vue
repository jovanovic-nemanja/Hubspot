<template>
  <div>
    <alert :options.sync="options" :show.sync="showAlert" @close="closeAlert" />
    <div class="action-panel-content">
      <div class="action-container">
        <div class="action-heading">
          <h2>Layouts</h2>
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
              >Layout Name</th>
              <th class scope="col">Date</th>
              <th class="action" scope="col"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(layout, idx) in layouts" :key="layout.name">
              <td class="module-name-table" scope="row">
                {{ layout.name }}
              </td>
              <td>{{ layout.created_at }}</td>
              <td>
                <button
                  class="btn-sm"
                  :class="'btn-delete'"
                  @click="deletelayout(layout.id, idx)"
                  ><i class="fa fa-trash-o" aria-hidden="true"></i>
                </button>
                <button
                  class="btn-sm"
                  :class="'btn-primary'"
                  @click="editlayout(layout.id, idx)"
                  >Open
                </button>
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
import JQuery from "jquery";
let $ = JQuery;

export default {
  name: "Layouts",
  props:{
    updated: Boolean
  },

  computed: mapGetters({
    authUser: "auth/user",
    billingStatus: "billing/status"
  }),
  watch:{
    updated(){
      this.getLayouts();
    }
  },

  components: {
    Alert
  },

  data: () => ({
    layouts: [],
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
    getLayouts(params) {
        axios.get("/api/v1/layouts", { params }).then(res => {
            this.layouts = res.data;
        })
        .catch(err => {
            this.showAlert = true;
            this.options.content = err.response.data.message;
            this.options.type = "danger";
        });
        this.$emit("reset");
    },

    editlayout(layoutId) {
        const id =  this.$route.params.id;
        this.$router.push(`/portals-main/${id}/${layoutId}/1`);
        $('.close-panel-btn').click();
    },

    deletelayout(id) {
      this.$swal({
        title: "Confirm Delete?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
        cancelButtonText: "No",
        reverseButtons: true
      }).then(result => {
        if (result.value) {
          axios.delete("/api/v1/layouts/" + id).then(res => {
            this.getLayouts();
          }) 
          .catch(err => {
            this.showAlert = true;
            this.options.content = err.response.data.message;
            this.options.type = "danger";
          });  
        } else if (result.dismiss === "cancel") {
          
        }
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
          this.getLayouts({
            order_name: "name",
            order_value: orderVal
          });
        } else if (orderVal == "asc") {
          this.nameOrderVal = "desc";
          this.nameOrder = {
            desc: false,
            asc: true
          };
          this.getLayouts({
            order_name: "name",
            order_value: orderVal
          });
        }
      }
    },

    search() {
      if (this.keyword.length > 1) {
        this.getLayouts({
          keyword: this.keyword
        });
      } else {
        this.getLayouts();
      }
    },

    closeAlert() {
      this.options = {};
      this.showAlert = false;
    }
  },

  beforeMount: function() {
    this.getLayouts();
  }
};
</script>
