<template>
  <div>
    <alert :options.sync="options" :show.sync="showAlert" @close="closeAlert" />
    <div class="setup-view">
      <!-- Login Form -->
      <div class="container">
        <div class="row">
          <div class="col-md-6 offset-md-3">
            <div class="setup-form-container">
              <img src="/images/icon-sr-dark.svg" alt="Sprocket Rocket Icon" />
              <h2>Setup Your Account</h2>
              <form @submit.prevent="acceptInvitation" @keydown="form.onKeydown($event)">
                <div class="form-group">
                  <label for="inputAgencyName">Account Name</label>
                  <input
                    v-model="form.agency_name"
                    :class="{ 'is-invalid': form.errors.has('agency_name') }"
                    type="name"
                    name="agency_name"
                    class="form-control"
                    id="inputAgencyName"
                  />
                  <has-error :form="form" field="agency_name" />
                </div>
                <div class="form-group">
                  <label for="inputName">Name</label>
                  <input
                    v-model="form.name"
                    :class="{ 'is-invalid': form.errors.has('name') }"
                    type="text"
                    name="name"
                    class="form-control"
                    id="inputName"
                  />
                  <has-error :form="form" field="name" />
                </div>
                <div class="form-group">
                  <label for="inputEmail">Email</label>
                  <input
                    v-model="form.email"
                    :class="{ 'is-invalid': form.errors.has('email') }"
                    type="email"
                    name="email"
                    class="form-control"
                    id="inputEmail"
                  />
                  <has-error :form="form" field="email" />
                </div>
                <div class="form-group">
                  <label for="inputPassword">Password</label>
                  <input
                    v-model="form.password"
                    :class="{ 'is-invalid': form.errors.has('password') }"
                    type="password"
                    name="password"
                    class="form-control"
                    id="inputPassword"
                  />
                  <has-error :form="form" field="password" />
                </div>
                <v-button
                  :loading="form.busy"
                  type="submit"
                  class="btn btn-primary"
                >{{ $t('setup') }}</v-button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Vue from "vue";
import VueRouter from "vue-router";
import Form from "vform";
import axios from "axios";
import { mapGetters } from "vuex";
import Alert from "../components/Alert";

export default {
  middleware: "guest",

  layout: "basic",

  metaInfo() {
    return { title: "Setup Agency" };
  },

  components: {
    Alert
  },

  data: () => ({
    title: window.config.appName,
    form: new Form({
      agency_name: "",
      name: "",
      email: "",
      password: "",
      plan_id: "",
      redirect_url: "",
      token: ""
    }),
    loginForm: new Form({
      email: "",
      password: ""
    }),
    options: {},
    showAlert: false
  }),

  created() {
    this.getInvitationDetail(this.$route.query.token);
    this.form.token = this.$route.query.token;
  },

  methods: {
    async getInvitationDetail(token) {
      try {
        const res = await axios.get("/api/v1/agencies/invite/" + token);
        this.form.email = res.data.admin_email;
        this.form.agency_name = res.data.agency_name;
        this.form.name = res.data.admin_name;
        this.form.plan_id = res.data.plan_id;
        this.form.redirect_url = res.data.redirect_url;
      } catch (error) {
        this.showAlert = true;
        this.options.content = err.response.data.message;
        this.options.type = "danger";
      }
    },

    async acceptInvitation() {
      try {
        // Submit the form.
        const registerData = await this.form.post(
          "/api/v1/agencies/accept-invitation"
        );

        this.loginForm.password = this.form.password;
        this.loginForm.email = this.form.email;

        const { data } = await this.loginForm.post("/api/login");

        // Save the token.
        this.$store.dispatch("auth/saveToken", {
          token: data.token,
          remember: this.remember
        });

        // Fetch the user.
        await this.$store.dispatch("auth/fetchUser");

        // Fetch the agency billing.
        await this.$store.dispatch("billing/fetchAgencyBilling");

        if (data.token) {
          const loggedUser = await axios.get("/api/v1/users/auth");

          if (loggedUser.data.agencies.portals_count > 0) {
            this.$router.push({ name: "portals-main" });
          } else {
            this.$router.push({ name: "portals-start" });
          }
        }
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

  computed: mapGetters({
    authenticated: "auth/check"
  })
};
</script>