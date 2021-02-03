<template>
  <div>
    <alert :options.sync="options" :show.sync="showAlert" @close="closeAlert" />
    <div class="action-panel-content account-details">
      <div class="action-container">
        <div class="action-heading">
          <h2>Account Details</h2>
        </div>
        <form id="account-details" @submit.prevent="updateAccount">
          <div class="row sr-forms">
            <div class="col">
              <label for="name">Name</label>
              <input
                id="name"
                :class="{ 'is-invalid': form.errors.has('name') }"
                type="text"
                class="form-control"
                v-model="form.name"
              />
              <has-error :form="form" field="name" />
            </div>
            <div class="col" v-if="isCustomLogo">
              <label for="files">Agency Logo</label>
              <!-- <img :src="form.cover" /> -->
              <input
                id="files"
                type="file"
                class="add-file"
                v-on:change="onImageChange"
                style="color:transparent;"
              />
              <label id="fileLabel">{{ filename }}</label>
            </div>
            <div class="col" v-if="!isCustomLogo"></div>
          </div>
          <div class="row sr-forms">
            <div class="col">
              <label for="email">Email</label>
              <input
                id="email"
                :class="{ 'is-invalid': form.errors.has('email') }"
                type="email"
                class="form-control"
                v-model="form.email"
              />
              <has-error :form="form" field="email" />
            </div>
            <div class="col"></div>
          </div>
          <div class="row sr-forms">
            <div class="col">
              <label for="password">Password</label>
              <a id="edit-password" class="edit-password">Edit</a>
              <input
                id="password"
                type="password"
                class="form-control"
                value="password"
                v-model="form.password"
                readonly
              />
            </div>
            <div class="col"></div>
          </div>
          <button type="submit" class="btn btn-primary">Save</button>
        </form>
      </div>
      <div class="delete-account-option show">
        <div class="close-icon">
          <img src="/images/icon-close.svg" />
        </div>
        <div class="delete-message">
          Delete account
          <div class="delete-warning">
            Deleting your account will remove all the data associated with your
            account. If you are on an Agency Plan, deleting your agency account
            will remove all Users under your account and disconnect all Portals
            from Sprocket Rocket.
          </div>
          <a
            @click="deleteAgency()"
            v-if="isAdmin || isAgencyAdmin"
            class="btn btn-warning"
            href="#"
          >Delete Agency Account</a>&nbsp;&nbsp;
          <a @click="deleteMe()" class="btn btn-warning" href="#">Delete My User Account</a>
        </div>
        <a class="delete-toggle">Delete Account</a>
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
let $ = JQuery;

export default {
  name: "AccountDetails",

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
    showDeleteAccount: false,
    user: {},
    agencies: {},
    options: {},
    showAlert: false,
    filename: "No file chosen",
    isCustomLogo: false,
    isLoading: false,

    form: new Form({
      name: "",
      email: "",
      password: ""
    })
  }),

  methods: {
    async getUser() {
      const response = await axios.get(
        "/api/v1/users/auth?include=roles,agencies"
      );
      this.user = response.data;

      this.form.name = response.data.name;
      this.form.email = response.data.email;
      this.form.role = response.data.roles.name;
      this.form.cover = response.data.agencies.cover;
    },

    updateAccount() {
      this.closeAlert();
      this.form
        .put("/api/v1/users/" + this.user.id)
        .then(res => {
          this.showAlert = true;
          this.options.content = "Account details have been updated!";
          this.options.type = "success";
        })
        .catch(err => {
          this.showAlert = true;
          this.options.content = err.response.data.message;
          this.options.type = "danger";
        });
    },

    deleteMe() {
      if (!this.isLoading) {
        this.isLoading = true;
        this.form
          .delete("/api/v1/users/me")
          .then(res => {
            // Redirect to login.
            window.location.href = "/";
          })
          .catch(err => {
            this.showAlert = true;
            this.options.content = err.response.data.message;
            this.options.type = "danger";
            this.isLoading = false;
          });
      }
    },

    deleteAgency() {
      if (!this.isLoading) {
        this.isLoading = true;
        this.form
          .delete("/api/v1/agencies/me")
          .then(res => {
            // Redirect to login.
            window.location.href = "/";
          })
          .catch(err => {
            this.showAlert = true;
            this.options.content = err.response.data.message;
            this.options.type = "danger";
            this.isLoading = false;
          });
      }
    },

    onImageChange(e) {
      let currentObj = this;

      this.filename = e.target.files[0].name;

      const config = {
        headers: { "content-type": "multipart/form-data" }
      };

      let formData = new FormData();
      formData.append("image", e.target.files[0]);

      axios
        .post("/api/v1/image-upload", formData, config)
        .then(res => {
          this.form.cover = res.data.image_name;

          this.updateAccount();
        })
        .catch(err => {
          this.showAlert = true;
          this.options.content = err.response.data.message;
          this.options.type = "danger";
        });
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
    this.getUser();

    $(".col a.edit-password").click(function() {
      $("#account-details #password").removeAttr("readonly");
    });

    $(".delete-toggle").click(function() {
      $(".delete-message").fadeIn(300);
      $(".delete-toggle").fadeOut(300);
      $(".delete-account-option .close-icon").fadeIn(300);
    });

    $(".delete-account-option .close-icon").click(function() {
      $(".delete-message").fadeOut(300);
      $(".delete-toggle").fadeIn(300);
      $(".delete-account-option .close-icon").fadeOut(300);
    });
  }
};
</script>
