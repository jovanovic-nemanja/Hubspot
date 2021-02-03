<template>
  <div>
    <alert :options.sync="options" :show.sync="showAlert" @close="closeAlert" />
    <div class="action-panel-content">
      <div class="action-container">
        <div class="action-heading">
          <h2>History</h2>
          <div>
            <input
              id="plans-search"
              @change="check($event)"
              v-model="showmypage"
              class="form-control"
              type="checkbox"
            /> Show only my pages
          </div>
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
              >Name</th>
              <th class scope="col">Username</th>
              <th class scope="col">Status</th>
              <th class scope="col">Date</th>
              <th class="action" scope="col"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(history, idx) in histories" :key="history.name">
              <td class="module-name-table" scope="row">
                {{ history.name }}
              </td>
              <td>{{ history.username }}</td>
              <td v-if="history.status == null">Draft</td>
              <td v-else>Published</td>
              <td>{{ history.updated_at }}</td>
              <td>
                <button
                  class="btn-sm"
                  :class="'btn-success'"
                  @click="openHistory(history.id, idx)"
                >Open</button>
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
  name: "History",

  props:{
    updated: Boolean
  },

  computed: mapGetters({
    authUser: "auth/user",
    billingStatus: "billing/status"
  }),

  watch:{
    updated(){
      this.getHistories();
    }
  },

  components: {
    Alert
  },

  data: () => ({
    histories: [],
    options: {},
    showAlert: false,
    keyword: "",
    showmypage: "",
    nameOrder: {
      desc: true,
      asc: false
    },
    nameOrderVal: "desc",
    isEligible: false
  }),

  methods: {
    check: function(e) {
      if (e.target.checked) { //checked
        this.showmypage = 1;
        this.getHistories({
          showmypage: this.showmypage,
          keyword: this.keyword
        });
      } else {
        this.showmypage = '';
        this.getHistories({
          showmypage: this.showmypage,
          keyword: this.keyword
        });
      }
    },

    getHistories(params) {
        axios.get("/api/v1/histories", { params }).then(res => {
            this.histories = res.data;
        })
        .catch(err => {
            this.showAlert = true;
            this.options.content = err.response.data.message;
            this.options.type = "danger";
        });
        this.$emit("reset");
    },

    openHistory(historyId) {
        const id =  this.$route.params.id;
        this.$router.push(`/portals-main/${id}/${historyId}/2`);
        $('.close-panel-btn').click();
    },

    search() {
      if (this.keyword.length > 1) {
        this.getHistories({
          keyword: this.keyword,
          showmypage: this.showmypage,
        });
      } else {
        this.getHistories({
          showmypage: this.showmypage,
          keyword: this.keyword
        });
      }
    },

    closeAlert() {
      this.options = {};
      this.showAlert = false;
    }
  },

  beforeMount: function() {
    this.getHistories();
  }
};
</script>
