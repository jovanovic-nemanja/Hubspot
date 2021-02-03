import Vue from "vue";
import store from "~/store";
import router from "~/router";
import i18n from "~/plugins/i18n";
import Drawer from "~/plugins/drawer";
import App from "~/components/App";
import vmodal from 'vue-js-modal';
import VS2 from 'vue-script2';
import VueDragula from 'vue-dragula';
import VueSweetalert2 from 'vue-sweetalert2';
import numeral from 'numeral';
import numFormat from 'vue-filter-number-format';
import VueIntercom from 'vue-intercom';
import VTooltip from 'v-tooltip';

import "~/plugins";
import "~/components";
import 'sweetalert2/dist/sweetalert2.min.css';

Vue.config.productionTip = false;
Vue.component('Drawer', Drawer);
Vue.filter('numFormat', numFormat(numeral));
Vue.use(vmodal);
Vue.use(VS2);
Vue.use(VueSweetalert2);
Vue.use(VueDragula);
Vue.use(VueIntercom, { appId: 'bfnwytm7' });
Vue.use(VTooltip);

/* eslint-disable no-new */
new Vue({
  i18n,
  store,
  router,
  ...App
});
