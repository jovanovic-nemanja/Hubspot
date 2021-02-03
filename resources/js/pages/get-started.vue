<template>
  <div>
    <alert :options.sync="options" :show.sync="showAlert" @close="closeAlert" />
    <div class="setup-view">
      <div class="container">
        <div class="row">
          <div class="col-md-6 offset-md-3">
            <div class="setup-form-container">
              <img src="/images/icon-sr-dark.svg" alt="Sprocket Rocket Icon" />
              <h2>Get Started</h2>
              <form @submit.prevent="sendInvitation" @keydown="form.onKeydown($event)">
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
                <v-button :loading="form.busy" type="submit" class="btn btn-primary">Send</v-button>
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
    return { title: "Invite" };
  },

  components: {
    Alert
  },

  data: () => ({
    form: new Form({
      name: "",
      email: ""
    }),
    options: {},
    showAlert: false
  }),

  methods: {
    async sendInvitation() {
      try {
        // Submit the form.
        const { data } = await this.form.post("/api/v1/agencies/invite");

        this.showAlert = true;
        this.options.content = "Invitation Sent";
        this.options.type = "success";
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

  created() {}
};
</script>
