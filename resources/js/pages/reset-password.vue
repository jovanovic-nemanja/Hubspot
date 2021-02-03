<template>
  <div>
    <alert :options.sync="options" :show.sync="showAlert" @close="closeAlert" />
    <div class="setup-view">
      <div class="container">
        <div class="row">
          <div class="col-md-6 offset-md-3">
            <div class="setup-form-container">
              <img src="/images/icon-sr-dark.svg" alt="Sprocket Rocket Icon" />
              <h2>Setup New Password</h2>
              <form @submit.prevent="setup" @keydown="form.onKeydown($event)">
                <div class="form-group">
                  <label for="password">Password</label>
                  <input v-model="form.password" :class="{ 'is-invalid': form.errors.has('password') }" type="password" name="password" class="form-control" id="password" />
                  <has-error :form="form" field="password" />
                </div>
                <div class="form-group">
                  <label for="password_confirmation">Password Confirmation</label>
                  <input v-model="form.password_confirmation" :class="{ 'is-invalid': form.errors.has('password_confirmation') }" type="password" name="password_confirmation" class="form-control" id="password_confirmation" />
                  <has-error :form="form" field="password_confirmation" />
                </div>
                <div class="clearfix"></div>
                <v-button :loading="form.busy" type="submit" class="btn btn-primary">Save new password</v-button>
              </form>
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
    return { title: "Setup new password" };
  },

  components: {
    Alert
  },

  computed: mapGetters({
    authenticated: "auth/check"
  }),

  data: () => ({
    title: window.config.appName,
    form: new Form({
      password: "",
      password_confirmation: "",
      token: ""
    }),
    options: {},
    showAlert: false
  }),

  methods: {
    setup() {
      this.form.post("/api/password/setup")
        .then(res => {
          this.form.reset();
          this.$router.push({ name: "welcome", query: { reset_password: 'success' } });
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

  created() {
    this.form.token = this.$route.query.token;
  }
};

</script>
