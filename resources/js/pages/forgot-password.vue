<template>
  <div>
    <alert :options.sync="options" :show.sync="showAlert" @close="closeAlert" />
    <div class="setup-view">
      <div class="container">
        <div class="row">
          <div class="col-md-6 offset-md-3">
            <div class="setup-form-container">
              <img src="/images/icon-sr-dark.svg" alt="Sprocket Rocket Icon" />
              <h2>Forgot your password?</h2>
              <p>Enter your email address below and we’ll send you password reset instructions.</p>
              <form @submit.prevent="resetPassword" @keydown="form.onKeydown($event)">
                <div class="form-group">
                  <label for="inputName">Email</label>
                  <input v-model="form.email" :class="{ 'is-invalid': form.errors.has('email') }" type="email" name="email" class="form-control" id="inputName" />
                  <has-error :form="form" field="email" />
                </div>
                <div class="mt-2 mb-2 text-center">
                  
                </div>
                <v-button :loading="form.busy" type="submit" class="btn btn-primary">Email me reset instruction</v-button>
              </form>
            </div>
            <div class="mt-2 mb-2 text-center">
              <router-link :to="{ name: 'welcome' }">
                <span class="small">Never mind, go back</span>
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
    return { title: "Forgot Password" };
  },

  components: {
    Alert
  },

  data: () => ({
    title: window.config.appName,
    form: new Form({
      email: ""
    }),
    options: {},
    showAlert: false
  }),

  methods: {
    resetPassword() {
      this.form.post("/api/password/reset")
        .then(res => {
          this.form.reset();
          this.showAlert = true;
          this.options.content = "Check your email for reset instructions.";
          this.options.type = "success";
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

  computed: mapGetters({
    authenticated: "auth/check"
  })
};

</script>
