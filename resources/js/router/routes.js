function page(path) {
  return () =>
    import(/* webpackChunkName: '' */ `~/pages/${path}`).then(
      m => m.default || m
    );
}

export default [
  {
    path: "/",
    name: "welcome",
    component: page("welcome.vue")
  },
  {
    path: "/get-started",
    name: "get-started",
    component: page("get-started.vue")
  },
  {
    path: "/setup-account",
    name: "setup-account",
    component: page("register.vue")
  },
  {
    path: "/auto-login",
    name: "auto-login",
    component: page("autologin.vue")
  },
  {
    path: "/accept-invitation",
    name: "accept-invitation",
    component: page("register-as-agency.vue")
  },
  {
    path: "/forgot-password",
    name: "forgot-password",
    component: page("forgot-password.vue")
  },
  {
    path: "/reset-password",
    name: "reset-password",
    component: page("reset-password.vue")
  },
  {
    path: "/portals-main/custom-page/complete",
    name: "custom-page-add-complete",
    component: page("custom-page-add-complete.vue")
  },
  {
    path: "/portals-main/:id",
    name: "portals-detail",
    component: page("portals-detail.vue")
  },
  {
    path: "/portals-main/:id/:layoutId/:whichId",
    name: "portals-detail",
    component: page("portals-detail.vue")
  },
  {
    path: "/portals-main",
    name: "portals-main",
    component: page("portals-main.vue")
  },
  {
    path: "/portals-start",
    name: "portals-start",
    component: page("portals-start.vue")
  },
  {
    path: "/sr-setup-installer",
    name: "sr-setup-installer",
    component: page("sr-setup-installer.vue")
  },
  {
    path: "/sr-setup",
    name: "sr-setup",
    component: page("sr-setup.vue")
  },
  {
    path: "/sr-update",
    name: "sr-update",
    component: page("sr-update.vue")
  },
  {
    path: "/sr-update-installer",
    name: "sr-update-installer",
    component: page("sr-update-installer.vue")
  },
  {
    path: "*",
    component: page("errors/404.vue")
  }
];
