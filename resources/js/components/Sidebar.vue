<template>
  <!-- Left Sidebar -->
  <nav class="left-panel d-none d-md-block sidebar activated">
    <div class="sidebar-sticky">
      <form class="form-inline sidebar-search" onkeydown="return event.key != 'Enter';">
        <input
          id="sidebar-search"
          v-on:keyup="search"
          v-model="keyword"
          class="form-control"
          type="search"
          placeholder="Search"
          aria-label="Search"
          data-item="searchResult"
          @mouseover="hoverAction('searchResult', 'over')" 
        />
      </form>
      <ul class="nav flex-column">
        <li
          v-for="module in modules"
          class="nav-item"
          :key="module.key"
          v-bind:data-item="module.key"
          @mouseover="hoverAction(module.key, 'over')" 
          @mouseout="hoverAction(module.key, 'out')" 
        >
          <a class="nav-link" href="#">{{ module.name }}</a>
        </li>
      </ul>
    </div>
    <div class="sidebar-sub-menu">
      <ul @mouseover="hoverAction('searchResult', 'over')" :class="{show: searchResult}" id="searchResult">
        <draggable
          class="dragArea list-group"
          v-bind="dragOptions"
          :sort="false"
          :list="modulesSearch"
          :group="{ name: 'modules', pull: 'clone', put: false }"
          :forceFallback="true"
        >
          <li
            v-for="module in modulesSearch"
            :key="module.custom_module.name"
            title="Click and drag module into page builder"
            v-bind:data-module-name="module.custom_module.name"
            v-on:dragstart="dragstart()"
          >
            <img v-bind:src="module.custom_module.module_preview" />
            <i
              v-if="!isEligible && !module.is_free"
              style="display: inline-block;"
              class="fa fa-lock"
              aria-hidden="true"
              title="Module Group"
            ></i>
            {{ module.custom_module.name }}
          </li>
        </draggable>
      </ul>
      <ul @mouseover="hoverAction('navigation', 'over')" @mouseout="hoverAction('navigation', 'out')" :class="{show: navigation}" id="navigation">
        <draggable
          class="dragArea list-group"
          v-bind="dragOptions"
          :sort="false"
          :list="navigationModules"
          :group="{ name: 'modules', pull: 'clone', put: false }"
          :forceFallback="true"
        >
          <li
            v-for="module in navigationModules"
            :key="module.custom_module.name"
            title="Click and drag module into page builder"
            data-module-name="SR Navigation 1"
            v-on:dragstart="dragstart()"
          >
            <img v-bind:src="module.custom_module.module_preview" />
            <i
              v-if="!isEligible && !module.is_free"
              style="display: inline-block;"
              class="fa fa-lock"
              aria-hidden="true"
              title="Module Group"
            ></i>
            {{ module.custom_module.name }}
          </li>
        </draggable>
      </ul>
      <ul @mouseover="hoverAction('hero', 'over')" @mouseout="hoverAction('hero', 'out')" :class="{show: hero}" id="hero">
        <draggable
          class="dragArea list-group"
          v-bind="dragOptions"
          :sort="false"
          :list="heroModules"
          :group="{ name: 'modules', pull: 'clone', put: false }"
          :forceFallback="true"
        >
          <li
            v-for="module in heroModules"
            :key="module.custom_module.name"
            title="Click and drag module into page builder"
            data-module-name="SR Hero 1"
            v-on:dragstart="dragstart()"
          >
            <img v-bind:src="module.custom_module.module_preview" />
            <i
              v-if="!isEligible && !module.is_free"
              style="display: inline-block;"
              class="fa fa-lock"
              aria-hidden="true"
              title="Module Group"
            ></i>
            {{ module.custom_module.name }}
          </li>
        </draggable>
      </ul>
      <ul @mouseover="hoverAction('footer', 'over')" @mouseout="hoverAction('footer', 'out')" :class="{show: footer}" id="footer">
        <draggable
          class="dragArea list-group"
          v-bind="dragOptions"
          :sort="false"
          :list="footerModules"
          :group="{ name: 'modules', pull: 'clone', put: false }"
          :forceFallback="true"
        >
          <li
            v-for="module in footerModules"
            :key="module.custom_module.name"
            title="Click and drag module into page builder"
            data-module-name="SR Footer 1"
            v-on:dragstart="dragstart()"
          >
            <img v-bind:src="module.custom_module.module_preview" />
            <i
              v-if="!isEligible && !module.is_free"
              style="display: inline-block;"
              class="fa fa-lock"
              aria-hidden="true"
              title="Module Group"
            ></i>
            {{ module.custom_module.name }}
          </li>
        </draggable>
      </ul>
      <ul @mouseover="hoverAction('columns', 'over')" @mouseout="hoverAction('columns', 'out')" :class="{show: columns}" id="columns">
        <draggable
          class="dragArea list-group"
          v-bind="dragOptions"
          :sort="false"
          :list="columnsModules"
          :group="{ name: 'modules', pull: 'clone', put: false }"
          :forceFallback="true"
        >
          <li
            v-for="module in columnsModules"
            :key="module.custom_module.name"
            title="Click and drag module into page builder"
            data-module-name="SR Columns 1"
            v-on:dragstart="dragstart()"
          >
            <img v-bind:src="module.custom_module.module_preview" />
            <i
              v-if="!isEligible && !module.is_free"
              style="display: inline-block;"
              class="fa fa-lock"
              aria-hidden="true"
              title="Module Group"
            ></i>
            {{ module.custom_module.name }}
          </li>
        </draggable>
      </ul>
      <ul @mouseover="hoverAction('tabs', 'over')" @mouseout="hoverAction('tabs', 'out')" :class="{show: tabs}" id="tabs">
        <draggable
          class="dragArea list-group"
          v-bind="dragOptions"
          :sort="false"
          :list="tabsModules"
          :group="{ name: 'modules', pull: 'clone', put: false }"
          :forceFallback="true"
        >
          <li
            v-for="module in tabsModules"
            :key="module.custom_module.name"
            title="Click and drag module into page builder"
            data-module-name="SR Tabs 1"
            v-on:dragstart="dragstart()"
          >
            <img v-bind:src="module.custom_module.module_preview" />
            <i
              v-if="!isEligible && !module.is_free"
              style="display: inline-block;"
              class="fa fa-lock"
              aria-hidden="true"
              title="Module Group"
            ></i>
            {{ module.custom_module.name }}
          </li>
        </draggable>
      </ul>
      <ul @mouseover="hoverAction('offers', 'over')" @mouseout="hoverAction('offers', 'out')" :class="{show: offers}" id="offers">
        <draggable
          class="dragArea list-group"
          v-bind="dragOptions"
          :sort="false"
          :list="offersModules"
          :group="{ name: 'modules', pull: 'clone', put: false }"
          :forceFallback="true"
        >
          <li
            v-for="module in offersModules"
            :key="module.custom_module.name"
            title="Click and drag module into page builder"
            data-module-name="SR Offers 1"
            v-on:dragstart="dragstart()"
          >
            <img v-bind:src="module.custom_module.module_preview" />
            <i
              v-if="!isEligible && !module.is_free"
              style="display: inline-block;"
              class="fa fa-lock"
              aria-hidden="true"
              title="Module Group"
            ></i>
            {{ module.custom_module.name }}
          </li>
        </draggable>
      </ul>
      <ul @mouseover="hoverAction('cards', 'over')" @mouseout="hoverAction('cards', 'out')" :class="{show: cards}" id="cards">
        <draggable
          class="dragArea list-group"
          v-bind="dragOptions"
          :sort="false"
          :list="cardsModules"
          :group="{ name: 'modules', pull: 'clone', put: false }"
          :forceFallback="true"
        >
          <li
            v-for="module in cardsModules"
            :key="module.custom_module.name"
            title="Click and drag module into page builder"
            data-module-name="SR Cards 1"
            v-on:dragstart="dragstart()"
          >
            <img v-bind:src="module.custom_module.module_preview" />
            <i
              v-if="!isEligible && !module.is_free"
              style="display: inline-block;"
              class="fa fa-lock"
              aria-hidden="true"
              title="Module Group"
            ></i>
            {{ module.custom_module.name }}
          </li>
        </draggable>
      </ul>
      <ul @mouseover="hoverAction('social', 'over')" @mouseout="hoverAction('social', 'out')" :class="{show: social}" id="social">
        <draggable
          class="dragArea list-group"
          v-bind="dragOptions"
          :sort="false"
          :list="socialModules"
          :group="{ name: 'modules', pull: 'clone', put: false }"
          :forceFallback="true"
        >
          <li
            v-for="module in socialModules"
            :key="module.custom_module.name"
            title="Click and drag module into page builder"
            data-module-name="SR Social 1"
            v-on:dragstart="dragstart()"
          >
            <img v-bind:src="module.custom_module.module_preview" />
            <i
              v-if="!isEligible && !module.is_free"
              style="display: inline-block;"
              class="fa fa-lock"
              aria-hidden="true"
              title="Module Group"
            ></i>
            {{ module.custom_module.name }}
          </li>
        </draggable>
      </ul>
      <ul @mouseover="hoverAction('bling', 'over')" @mouseout="hoverAction('bling', 'out')" :class="{show: bling}" id="bling">
        <draggable
          class="dragArea list-group"
          v-bind="dragOptions"
          :sort="false"
          :list="blingModules"
          :group="{ name: 'modules', pull: 'clone', put: false }"
          :forceFallback="true"
        >
          <li
            v-for="module in blingModules"
            :key="module.custom_module.name"
            title="Click and drag module into page builder"
            data-module-name="SR Bling 1"
            v-on:dragstart="dragstart()"
          >
            <img v-bind:src="module.custom_module.module_preview" />
            <i
              v-if="!isEligible && !module.is_free"
              style="display: inline-block;"
              class="fa fa-lock"
              aria-hidden="true"
              title="Module Group"
            ></i>
            {{ module.custom_module.name }}
          </li>
        </draggable>
      </ul>
    </div>
  </nav>
</template>
<script>
import draggable from "vuedraggable";
import axios from "axios";
import { mapGetters } from "vuex";
import JQuery from "jquery";
let $ = JQuery;

export default {
  computed: {
    dragOptions() {
      return {
        ghostClass: "ghost-source",
        dragClass: "drag-source"
      };
    },
    ...mapGetters({
      authUser: "auth/user",
      billingStatus: "billing/status"
    })
  },

  components: {
    draggable
  },

  data: () => ({
    navigation: false,
    searchResult: false,
    hero: false,
    footer: false,
    columns: false,
    tabs: false,
    offers: false,
    cards: false,
    social: false,
    bling: false,
    appName: window.config.appName,
    modulesSearch: [],
    modules: [],
    navigationModules: [],
    heroModules: [],
    footerModules: [],
    columnsModules: [],
    tabsModules: [],
    offersModules: [],
    cardsModules: [],
    socialModules: [],
    blingModules: [],
    keyword: "",
    isEligible: false
  }),

  methods: {
    hoverAction(key, action) {
      if (action === 'over') {
        this[key] = true

        if (key !== 'searchResult') {
          this.searchResult = false
        }
      } else if (action === 'out') {
        this[key] = false
      }
    },

    async getModules() {
      const response = await axios.get("/api/v1/modules/categories");
      this.modules = response.data;
      this.navigationModules = this.modules.navigation.modules;
      this.heroModules = this.modules.hero.modules;
      this.footerModules = this.modules.footer.modules;
      this.columnsModules = this.modules.columns.modules;
      this.footerModules = this.modules.footer.modules;
      this.tabsModules = this.modules.tabs.modules;
      this.offersModules = this.modules.offers.modules;
      this.cardsModules = this.modules.cards.modules;
      this.socialModules = this.modules.social.modules;
      this.blingModules = this.modules.bling.modules;
    },

    searchModules(params) {
      if (this.$route.params.id !== undefined) {
        axios
          .get("/api/v1/modules/categories", {
            params
          })
          .then(res => {
            this.modulesSearch = [];
            for (let index = 0; index < res.data.data.length; index++) {
              this.modulesSearch.push(res.data.data[index].modules);
            }
          })
          .catch(err => {
            this.showAlert = true;
            this.options.content = err.response.data.message;
            this.options.type = "danger";
          });
      } else {
        this.showAlert = true;
        this.options.content = "Please select portal";
        this.options.type = "warning";
      }
    },

    search() {
      if (this.keyword.length > 1) {
        this.searchModules({
          keyword: this.keyword
        });
      } else {
        this.modulesSearch = [];
      }
    },

    dragstart: function() {
      $(".sidebar-sub-menu").removeClass("active");
      $(".sidebar ul li").removeClass("toggled");
    }
  },

  beforeMount: function() {
    this.getModules();

    if (
      (this.isAgencyAdmin || this.isUser) &&
      this.billingStatus == "suspend"
    ) {
      this.isSuspend = true;
    } else {
      if (
        ((this.authUser.agencies.plans.type == "paid" ||
          this.authUser.agencies.plans.type == "fixed") &&
          !this.isSuspend) ||
        (this.authUser.agencies.plans.type == "free" &&
          this.authUser.agencies.plans.is_hidden_plan == "yes" &&
          !this.isSuspend)
      ) {
        this.isEligible = true;
      }
    }
  },

  mounted: function() {
    $(".sidebar.activated ul.nav").mouseover(function() {
      $(".sidebar-sub-menu").addClass("active");
    });

    $(".sidebar.activated ul.nav").click(function() {
      $(".sidebar-sub-menu").addClass("active");
    });

    $(".main-navigation").mouseover(function() {
      $(".sidebar-sub-menu").removeClass("active");
      $(".sidebar ul li").removeClass("toggled");
    });

    $(".right-panel").mouseover(function() {
      $(".sidebar-sub-menu").removeClass("active");
      $(".sidebar ul li").removeClass("toggled");
    });

    $("#sidebar-search").click(function() {
      showMenuItems(this);
      $(".sidebar-sub-menu").addClass("active");
    });

    $("#sidebar-search").focus(function() {
      showMenuItems(this);
      $(".sidebar-sub-menu").addClass("active");
      $(".sidebar ul li").removeClass("toggled");
    });

    $(".sidebar ul li").mouseover(function() {
      $(this).addClass("toggled");
      $(this)
        .siblings()
        .removeClass("toggled");
    });

    $(".nav-item").mouseover(function(e) {
      showMenuItems(this);
    });

    function showMenuItems(menu) {
      var menuToShow = $(menu).attr("data-item");
      $("ul.show").removeClass("show");
      $("ul#" + menuToShow).addClass("show");
    }
  }
};
</script>
