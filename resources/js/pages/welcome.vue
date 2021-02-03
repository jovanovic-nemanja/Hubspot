<template>
  <div>
    <alert :options.sync="options" :show.sync="showAlert" @close="closeAlert" />
    <div class="setup-view">
      <div class="container">
        <div class="row">
          <div class="col-md-6 offset-md-3">
            <div class="setup-form-container">
              <img src="/images/icon-sr-dark.svg" alt="Sprocket Rocket Icon" />
              <h2>Sprocket Rocket Login</h2>
              <form @submit.prevent="login" @keydown="form.onKeydown($event)">
                <div class="form-group">
                  <label for="inputName">Email</label>
                  <input
                    v-model="form.email"
                    :class="{ 'is-invalid': form.errors.has('email') }"
                    type="email"
                    name="email"
                    class="form-control"
                    id="inputName"
                  />
                  <has-error :form="form" field="email" />
                </div>
                <div class="form-group">
                  <label for="InputPassword">Password</label>
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
                >{{ $t("login") }}</v-button>
              </form>
            </div>
            <div class="mt-2 mb-2 text-center">
              <router-link :to="{ name: 'forgot-password' }">
                <span class="small">Forgot your password?</span>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import Form from "vform";
import axios from "axios";
import { mapGetters } from "vuex";
import Alert from "../components/Alert";

export default {
  middleware: "guest",

  layout: "basic",

  metaInfo() {
    return { title: this.$t("login") };
  },

  components: {
    Alert
  },

  data: () => ({
    title: window.config.appName,
    form: new Form({
      email: "",
      password: ""
    }),
    remember: false,
    options: {},
    showAlert: false
  }),

  methods: {
    async login() {
      try {
        // Submit the form.
        const { data } = await this.form.post("/api/login");

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

          if (loggedUser.data.portals_count > 0) {
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
  }),

  created() {
    if (this.$route.query.reset_password == "success") {
      this.showAlert = true;
      this.options.content = "Password updated!";
      this.options.type = "success";
    }
  }
};
</script>
