<template>
  <div class="container-fluid">
    <div class="row flex-nowrap">
      <sidebar />
      <!-- Portals -->
      <div class="right-panel">
        <alert :options.sync="options" :show.sync="showAlert" @close="closeAlert" />
        <div class="inner-container" v-if="!addPage">
          <div class="row">
            <div class="col-md-8">
              <input
                type="text"
                class="form-control page-name"
                placeholder="Page Name"
                aria-label="Page Name"
                @change="changePageName"
                aria-describedby="basic-addon2"
                v-model="form.name"
              />
            </div>
            <div class="col-md-4 action">
              <button
                :disabled="isSuspend || moduleList.length <= 0"
                @click="createPage"
                class="btn btn-primary"
              >{{ buttonState }}</button>

              <button
                :disabled="isSuspendLayout || moduleList.length <= 0"
                @click="createLayout"
                class="btn btn-primary"
              >{{ layoutbuttonState }}</button>
            </div>
          </div>
          <div
            v-bind:class="{
              'main-container': moduleList.length <= 0,
              'main-container-preview': moduleList.length > 0
            }"
          >
            <div class="col-md-12">
              <div class="setup-message-modules" v-if="moduleList.length <= 0">
                <img src="/images/icon-drag-drop.svg" alt="Drag icon" />
                <p>Drag a module here to get started</p>
              </div>
              <draggable
                class="dragArea list-group"
                tag="ul"
                v-model="moduleList"
                group="modules"
                v-bind="dragOptions"
                @start="drag = true"
                @end="drag = false"
                @add="addItem"
                :forceFallback="true"
              >
                <transition-group type="transition" :name="!drag ? 'flip-list' : null">
                  <li
                    class="list-group-item"
                    v-for="(element, idx) in moduleList"
                    :key="element.custom_module.name"
                    @mouseover="hover = true"
                    @mouseleave="hover = false"
                  >
                    <i v-if="hover" class="fa fa-times close" @click="removeAt(idx)"></i>
                    <img v-bind:src="element.custom_module.module_preview" />
                  </li>
                </transition-group>
              </draggable>
            </div>
          </div>
        </div>
        <div class="inner-container" v-if="addPage">
          <div class="row">
            <div class="col-md-12">
              <div class="setup-message" v-if="isLoading">
                <h1>Adding Page...</h1>
              </div>
              <div class="setup-message" v-if="!isLoading">
                <h1>{{ pageName }} has been added!</h1>

                <p>
                  All modules have made it to your portal and your new page is
                  waiting for you here:
                  <br />
                  <a
                    class="btn btn-primary"
                    v-bind:href="
                      'https://app.hubspot.com/content/' +
                        portal.hub_id +
                        '/edit/' +
                        createPageResult.data.page_id +
                        '/content'
                    "
                    target="_blank"
                    rel="noopener noreferrer"
                  >Edit Page</a>
                  <a
                    class="btn btn-primary"
                    :href="$router.resolve({
                    name: 'portals-detail',
                    params: { id: portal.id }}).href"
                  >Add Another Page</a>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import draggable from "vuedraggable";
import { mapGetters } from "vuex";
import Sidebar from "~/components/Sidebar";
import Form from "vform";
import axios from "axios";
import Alert from "../components/Alert";
import JQuery from "jquery";
let $ = JQuery;

export default {
  middleware: "auth",

  props:{
    updated: Boolean
  },

  computed: {
    dragOptions() {
      return {
        animation: 200,
        group: "description",
        disabled: false,
        ghostClass: "ghost"
      };
    },
    ...mapGetters({
      authUser: "auth/user",
      billingStatus: "billing/status"
    })
  },

  watch: {
    $route: 'fetchLayout',
    moduleList(){
      if(this.auto_save_status) {

      }else{
        this.autoSave();
      }
    }
  },

  components: {
    Sidebar,
    Alert,
    draggable
  },

  data: () => ({
    moduleList: [],
    portal: {},
    pageName: "",
    pageTitle: "",
    createPageResult: [],
    form: new Form({
      name: ""      
    }),
    historyId: "",
    auto_save_status: false,
    buttonState: "Add Page",
    layoutbuttonState: "Save Layout",
    options: {},
    showAlert: false,
    isSuspend: false,
    isSuspendLayout: false,
    isLoading: false,
    addPage: false,
    hover: false,
    drag: false
  }),

  metaInfo() {
    return { title: this.$t("home") };
  },

  methods: {
    changePageName() {
      if(this.auto_save_status) {
        
      }else{
        this.autoSave();
      }
    },

    autoSave () {
      const whichId = this.$route.params.whichId;
      const layoutId = this.$route.params.layoutId;

      if(this.moduleList.length > 0 && whichId != 1) {
        const modules = [];
        this.moduleList.forEach(function(module) {
          var _module = {
            type: "custom_widget",
            body: {
              widget_name: module.custom_module.name
            }
          };

          modules.push(_module);
        });

        this.showAlert = false;
        this.options = {};
        this.isLoading = true;
        if(layoutId && whichId == 2) {  //when open button click in history drawer
          this.historyId = layoutId;
        }

        var date = new Date();
        var str = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
        if(this.form.name == "") {
          this.pageTitle = str;
        }else{
          this.pageTitle = this.form.name;
        }

        let pageData = {
          name: this.pageTitle,
          historyId: this.historyId,
          portal_id: this.$route.params.id,
          modules: this.moduleList
        };
        this.pageName = this.form.name;
        this.auto_save_status = true;
        
        axios
          .post("/api/v1/histories", pageData)
          .then(res => {
            this.showAlert = false;  
            this.options.content = "Success!";
            this.options.type = "success";
            this.createPageResult = res;
            this.historyId = res.data.id;

            this.auto_save_status = false;
            this.isLoading = false;
          })
          .catch(err => {
            this.showAlert = true;
            this.options.content = err.response.data.message;
            this.options.type = "danger";

            this.layoutbuttonState = "Add Layout";
            this.isLoading = false;
            this.addPage = false;
        });
      }
    },

    fetchLayout (){
      const layoutId = this.$route.params.layoutId;
      const whichId = this.$route.params.whichId;
      if(layoutId && whichId == 1) {  //layout case
        axios.get("/api/v1/layouts/" + layoutId).then(res => {
          this.form.name =  res.data.layouts.name;
          
          if(res.data.modules.length) {
            var arrs = res.data.modules;
            for(var i=0; i<arrs.length; i++) {
              const modules = JSON.parse(arrs[i].modules);
              modules.custom_module.module_preview = '/module-previews/' + this.convertToSlug(arrs[i].name) + '.jpg';
              arrs[i] = modules;
            }
          }
          this.isSuspend = false;
          this.addPage = false;
          this.moduleList = arrs;
          this.layoutbuttonState = "Update Layout";
        })
        .catch(err => {
          this.showAlert = true;
          this.options.content = err.response.data.message;
          this.options.type = "danger";
        });
      }else if(layoutId && whichId == 2) {  //history case
        axios.get("/api/v1/histories/" + layoutId).then(res => {
          this.form.name =  res.data.histories.name;
          
          if(res.data.modules.length) {
            var arrs = res.data.modules;
            for(var i=0; i<arrs.length; i++) {
              const modules = JSON.parse(arrs[i].modules);
              modules.custom_module.module_preview = '/module-previews/' + this.convertToSlug(arrs[i].name) + '.jpg';
              arrs[i] = modules;
            }
          }
          this.isSuspend = false;
          // this.addPage = false;
          this.layoutbuttonState = "Add Layout";
          this.moduleList = arrs;
        })
        .catch(err => {
          this.showAlert = true;
          this.options.content = err.response.data.message;
          this.options.type = "danger";
        });
      }
    },

    convertToSlug (Text) {
      return Text
          .toLowerCase()
          .replace(/ /g,'-')
          .replace(/[^\w-]+/g,'')
          ;
    },

    addItem(evt) {
      this.checkModulesList();
    },

    checkModulesList() {
      var paidModule = 0;

      for (let index = 0; index < this.moduleList.length; index++) {
        if (
          this.authUser.agencies.plans.type == "free" &&
          !this.moduleList[index].is_free
        ) {
          paidModule += 1;
        }
      }

      if (
        this.authUser.agencies.plans.type == "free" &&
        this.authUser.agencies.plans.is_hidden_plan == "no" &&
        paidModule > 0
      ) {
        this.isSuspend = false;
        this.showAlert = false;
        
        this.options.content =
          "Some of the modules you've added are not free and you need to upgrade your plan";
        this.options.type = "warning";
      } else if (
        this.authUser.agencies.plans.type == "free" &&
        this.authUser.agencies.plans.is_hidden_plan == "yes" &&
        paidModule > 0
      ) {
        this.isSuspend = false;
        this.closeAlert();
      } else {
        this.isSuspend = false;
        this.closeAlert();
      }
    },

    createPage() {
      if (this.form.name && this.moduleList.length > 0 && !this.isSuspend) {
        this.$swal({
          title: "Select Page Type",
          icon: "info",
          showCancelButton: true,
          confirmButtonText: "Website Page",
          cancelButtonText: "Landing Page",
          reverseButtons: true
        }).then(result => {
          if (result.value) {
            this.confirmCreatePage("site_page");
          } else if (result.dismiss === "cancel") {
            this.confirmCreatePage("landing_page");
          }
        });
      } else {
        if (!this.form.name) {
          this.showAlert = true;
          this.options.content = "Missing page name";
          this.options.type = "danger";
        }
      }
    },

    createLayout() {
      if (this.form.name && this.moduleList.length > 0 && !this.isSuspend) {
        const layoutId = this.$route.params.layoutId;
        const whichId = this.$route.params.whichId;
        if(layoutId && whichId == 1) {
          this.confirmUpdateLayout();
        }else
          this.confirmCreateLayout();
      } else {
        if (!this.form.name) {
          this.showAlert = true;
          this.options.content = "Missing page name";
          this.options.type = "danger";
        }
      }
    },

    confirmCreateLayout() {
      const modules = [];

      this.moduleList.forEach(function(module) {
        var _module = {
          type: "custom_widget",
          body: {
            widget_name: module.custom_module.name
          }
        };

        modules.push(_module);
      });

      this.showAlert = false;
      this.options = {};
      this.layoutbuttonState = "Saving Layout...";
      this.isLoading = true;

      let pageData = {
        name: this.form.name,
        portal_id: this.$route.params.id,
        modules: this.moduleList
      };
      this.pageName = this.form.name;

      axios
        .post("/api/v1/layouts", pageData)
        .then(res => {
          this.showAlert = true;
          this.options.content = "Your layout has been saved!";
          this.options.type = "success";
          this.createPageResult = res;

          this.layoutbuttonState = "Save Layout";
          this.isLoading = false;
          // this.form.reset();
        })
        .catch(err => {
          this.showAlert = true;
          this.options.content = err.response.data.message;
          this.options.type = "danger";

          this.layoutbuttonState = "Save Layout";
          this.isLoading = false;
          this.addPage = false;
        });
    },

    confirmUpdateLayout() {
      const modules = [];

      this.moduleList.forEach(function(module) {
        var _module = {
          type: "custom_widget",
          body: {
            widget_name: module.custom_module.name
          }
        };

        modules.push(_module);
      });

      this.showAlert = false;
      this.options = {};
      this.layoutbuttonState = "Updating Layout...";
      this.isLoading = true;
      const layoutId = this.$route.params.layoutId;

      let pageData = {
        name: this.form.name,
        id: layoutId,
        portal_id: this.$route.params.id,
        modules: this.moduleList
      };
      this.pageName = this.form.name;

      axios
        .post("/api/v1/layouts", pageData)
        .then(res => {
          this.showAlert = true;
          this.options.content = "Success!";
          this.options.type = "success";
          this.createPageResult = res;

          this.layoutbuttonState = "Update Layout";
          this.isLoading = false;
          // this.form.reset();
        })
        .catch(err => {
          this.showAlert = true;
          this.options.content = err.response.data.message;
          this.options.type = "danger";

          this.layoutbuttonState = "Update Layout";
          this.isLoading = false;
          this.addPage = false;
        });
    },

    confirmCreatePage(type) {
      const modules = [];

      this.moduleList.forEach(function(module) {
        var _module = {
          type: "custom_widget",
          body: {
            widget_name: module.custom_module.name
          }
        };

        modules.push(_module);
      });

      this.showAlert = false;
      this.options = {};
      this.buttonState = "Adding Page...";
      this.isLoading = true;
      this.addPage = true;

      const layoutId = this.$route.params.layoutId;
      const whichId = this.$route.params.whichId;
      
      if(layoutId && whichId == 2) {
        this.historyId = layoutId;
      }else{
        this.historyId = '';
      }

      let pageData = {
        name: this.form.name,
        portal_id: this.$route.params.id,
        modules: this.moduleList,
        history_id: this.historyId,
        subcategory: type
      };
      this.pageName = this.form.name;
      
      axios
        .post("/api/v1/pages", pageData)
        .then(res => {
          this.showAlert = true;
          this.options.content = "Success!";
          this.options.type = "success";
          this.createPageResult = res;

          this.buttonState = "Add Page";
          this.isLoading = false;
          // this.form.reset();

          const id =  this.$route.params.id;
          this.$router.push(`/portals-main/${id}`);
        })
        .catch(err => {
          this.showAlert = true;
          this.options.content = err.response.data.message;
          this.options.type = "danger";

          this.buttonState = "Add Page";
          this.isLoading = false;
          this.addPage = false;
        });
    },

    getPortalById(id) {
      this.buttonState = "Loading";
      this.isLoading = true;

      const self = this;
      axios
        .get("/api/v1/portals/" + id)
        .then(res => {
          if (res.data.portal_status) {
            self.portal = res.data;
            self.buttonState = "Add Page";
            self.isLoading = false;

            if (self.isUser) {
              if (self.authUser.id != self.portal.user_id) {
                self.$router.push({ name: "portals-main" });
              }
            }

            localStorage.portalData = JSON.stringify(res.data);
          } else {
            localStorage.portalData = JSON.stringify(res.data);
            self.$router.push({ name: "sr-setup" });
          }
        })
        .catch(err => {
          self.isLoading = false;
          self.buttonState = "Add Page";
        });
    },

    closeAlert() {
      this.options = {};
      this.showAlert = false;
    },

    removeAt(idx) {
      this.moduleList.splice(idx, 1);

      this.checkModulesList();
    }
  },

  created: function() {
    this.fetchLayout();
  },

  beforeMount: function() {
    this.isAdmin = this.authUser.roles.name == "admin" ? true : false;
    this.isAgencyAdmin =
      this.authUser.roles.name == "agency-admin" ? true : false;
    this.isUser = this.authUser.roles.name == "user" ? true : false;
    this.isSuspend = this.authUser.agencies.status == "suspend" ? true : false;

    this.getPortalById(this.$route.params.id);

    if (this.isAgencyAdmin && this.billingStatus == "suspend") {
      this.isSuspend = true;

      this.showAlert = true;
      this.options.content =
        "Please update your Billing Information to continue using Sprocket Rocket.";
      this.options.type = "warning";
    } else if (this.isUser && this.billingStatus == "suspend") {
      this.isSuspend = true;

      this.showAlert = true;
      this.options.content =
        "Please ask your agency admin to update their Billing Information to continue using Sprocket Rocket.";
      this.options.type = "warning";
    }
  },

  mounted: function() {
    this.$root.$on("addPortalsError", err => {
      this.showAlert = true;
      this.options.content = err.response.data.message;
      this.options.type = "danger";
    });
  }
};
</script>

<style>
</style>
