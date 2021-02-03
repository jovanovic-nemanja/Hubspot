<template>
  <div>
  </div>
</template>

<script>
import Vue from "vue";
import VueRouter from "vue-router";
import Form from "vform";
import axios from "axios";
import { mapGetters } from "vuex";

export default {
    middleware: "guest",

    layout: "basic",

    metaInfo() {
        return { title: this.$t("home") };
    },

    data: () => ({
        loginForm: new Form({
            email: "",
            token: "",
            password: ""
        })
    }),

    created() {
        
    },

    methods: {
        async login() {
            const { data } = await this.loginForm.post("/api/login");
            
            // Save the token.
            this.$store.dispatch("auth/saveToken", {
                token: data.token,
                remember: this.remember
            });

            // Fetch the user.
            await this.$store.dispatch("auth/fetchUser");

            if (data.token) {
                const loggedUser = await axios.get("/api/v1/users/auth");

                if (loggedUser.data.agencies.portals_count > 0) {
                    this.$router.push({ name: "portals-main" });
                } else {
                    this.$router.push({ name: "portals-start" });
                }
            }
        }
    },

    mounted() {
        this.loginForm.email = decodeURIComponent(this.$route.query.email);
        this.loginForm.password = decodeURIComponent(this.$route.query.pass);
        this.login();
    },

    computed: mapGetters({
        authenticated: "auth/check"
    })
};
</script>