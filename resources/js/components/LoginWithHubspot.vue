<template>
  <button @click="login" class="btn btn-primary">Add Portal</button>
</template>

<script>
import axios from "axios";

export default {
  name: "LoginWithHubspot",

  computed: {
    url: () => `/api/oauth/hubspot`,
  },

  mounted() {
    window.addEventListener("message", this.onMessage, false);
  },

  beforeDestroy() {
    window.removeEventListener("message", this.onMessage);
  },

  methods: {
    async login() {
      const newWindow = openWindow("", this.$t("login"));

      const url = await this.$store.dispatch("auth/fetchOauthUrl", {
        provider: "hubspot",
      });

      newWindow.location.href = url;
    },

    /**
     * @param {MessageEvent} e
     */
    onMessage(e) {
      let self = this;

      if (e.data.provider === "hubspot") {
        let integrateData = {
          provider: e.data.provider,
          provider_user_id: e.data.provider_user_id,
          access_token: e.data.access_token,
          refresh_token: e.data.refresh_token,
        };

        axios
          .post("/api/oauth/integrate", integrateData)
          .then(function (res) {
            let portalData = {
              name: e.data.hub_domain,
              hub_id: e.data.hub_id,
              hub_url: e.data.hub_domain,
              oauth_provider_id: res.data.id,
            };

            axios
              .post("/api/v1/portals", portalData)
              .then(function (res) {
                if (res.data.portal_setup_status) {
                  self.$store.dispatch("auth/fetchUser");

                  self.$router.push({ name: "portals-main" });

                  // self.$root.$emit("loadPortals");

                  // self.$root.$emit("reloadPage");
                } else {
                  localStorage.portalData = JSON.stringify(res.data);

                  self.$router.push({ name: "sr-setup" });
                }
              })
              .catch(function (err) {
                self.$store.dispatch("auth/fetchUser");

                self.$root.$emit("addPortalsError", err);
              });
          })
          .catch(function (err) {
            self.$root.$emit("addPortalsError", err);
          });
      }
    },
  },
};

/**
 * @param  {Object} options
 * @return {Window}
 */
function openWindow(url, title, options = {}) {
  if (typeof url === "object") {
    options = url;
    url = "";
  }

  options = { url, title, width: 600, height: 720, ...options };

  const dualScreenLeft =
    window.screenLeft !== undefined ? window.screenLeft : window.screen.left;
  const dualScreenTop =
    window.screenTop !== undefined ? window.screenTop : window.screen.top;
  const width =
    window.innerWidth ||
    document.documentElement.clientWidth ||
    window.screen.width;
  const height =
    window.innerHeight ||
    document.documentElement.clientHeight ||
    window.screen.height;

  options.left = width / 2 - options.width / 2 + dualScreenLeft;
  options.top = height / 2 - options.height / 2 + dualScreenTop;

  const optionsStr = Object.keys(options)
    .reduce((acc, key) => {
      acc.push(`${key}=${options[key]}`);
      return acc;
    }, [])
    .join(",");

  const newWindow = window.open(url, title, optionsStr);

  if (window.focus) {
    newWindow.focus();
  }

  return newWindow;
}
</script>
