<template>
  <modal name="welcome" width="640" height="592">
    <div class="modal-content" style="border: 0">
      <div class="modal-header" style="padding: 0">
        <div class="embed-responsive embed-responsive-16by9" style="padding: 0">
          <script2 type="text/javascript" defer src="https://play.vidyard.com/embed/v4.js"></script2>
          <img style="width: 100%; margin: auto; display: block;" class="vidyard-player-embed"
            src="https://play.vidyard.com/tGDj866Fq6vX82woeFokMb.jpg" data-uuid="tGDj866Fq6vX82woeFokMb" data-v="4"
            data-type="inline" />

        </div>
      </div>
      <div class="modal-body welcome-modal-body">
        <h2>Hey {{ name }}!<img class="wave-emoji" src='/images/waving-emoji.png' /></h2>
        <p>Welcome to Sprocket Rocket!</p>
        <button type="button" class="btn btn-primary" @click="hide" data-dismiss="modal">Let's Do This</button>
      </div>
    </div>
  </modal>
</template>

<script>
import { mapGetters } from "vuex";

export default {
  name: 'StartUp',

  computed: mapGetters({
    authUser: "auth/user"
  }),

  data: () => ({
    name: ""
  }),

  beforeMount: function() {
    this.name = this.authUser.name;
  },

  methods: {
    hide() {
      this.$modal.hide('welcome');
    }
  }
}
</script>