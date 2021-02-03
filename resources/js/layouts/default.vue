<template>
  <div class="main-layout">
    <navbar v-if="!showPortal" />
    <navbar-with-portal v-if="showPortal" />

    <div>
      <child />
    </div>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
import Navbar from "~/components/Navbar";
import NavbarWithPortal from "~/components/NavbarWithPortal";

export default {
  name: "MainLayout",

  components: {
    Navbar,
    NavbarWithPortal,
  },

  computed: mapGetters({
    authUser: "auth/user",
  }),

  data: () => ({
    showPortal: false,
  }),

  mounted() {
    this.$intercom.boot({
      user_id: this.authUser.id,
      name: this.authUser.name,
      email: this.authUser.email,
    });
    // this.$intercom.show();
  },

  watch: {
    $route() {
      if (this.$route.name == "portals-detail" || this.$route.name == "sr-update" || this.$route.name == "sr-update-installer") {
        this.showPortal = true;
      } else {
        this.showPortal = false;
      }
    },
  },
};
</script>
