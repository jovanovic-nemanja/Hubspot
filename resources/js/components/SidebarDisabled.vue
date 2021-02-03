<template>
  <!-- Left Sidebar -->
  <nav class="left-panel d-none d-md-block sidebar deactivated">
    <div class="sidebar-sticky">
      <form class="form-inline sidebar-search">
        <input
          id="sidebar-search"
          class="form-control"
          type="search"
          placeholder="Search"
          aria-label="Search"
          data-item="search-results"
          disabled
        />
      </form>
      <ul class="nav flex-column">
        <li class="nav-item" data-item="navigation">
          <a class="nav-link" href="#">Navigation</a>
        </li>
        <li class="nav-item" data-item="hero">
          <a class="nav-link" href="#">Hero</a>
        </li>
        <li class="nav-item" data-item="footer">
          <a class="nav-link" href="#">Footer</a>
        </li>
        <li class="nav-item" data-item="columns">
          <a class="nav-link" href="#">Columns</a>
        </li>
        <li class="nav-item" data-item="tabs">
          <a class="nav-link" href="#">Tabs</a>
        </li>
        <li class="nav-item" data-item="offers">
          <a class="nav-link" href="#">Offers</a>
        </li>
        <li class="nav-item" data-item="cards">
          <a class="nav-link" href="#">Cards</a>
        </li>
        <li class="nav-item" data-item="social">
          <a class="nav-link" href="#">Social</a>
        </li>
        <li class="nav-item" data-item="bling">
          <a class="nav-link" href="#">Bling</a>
        </li>
      </ul>
    </div>
    <div class="sidebar-sub-menu">
      <ul id="search-results">
        <span style="text-align:center;">Search Results:</span>
      </ul>
      <ul id="navigation" class>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Navigation 1"
        >
          <img src="/images/sr-hero-1.png" />
          SR Navigation 1
        </li>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Navigation 2"
        >
          <img src="/images/sr-hero-1.png" />
          SR Navigation 2
        </li>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Navigation 3"
        >
          <img src="/images/sr-hero-1.png" />
          SR Navigation 3
        </li>
      </ul>
      <ul id="hero" class>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Hero 1"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Hero 1
        </li>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Hero 2"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Hero 2
        </li>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Hero 3"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Hero 3
        </li>
      </ul>
      <ul id="footer" class>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Footer 1"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Footer 1
        </li>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Footer 2"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Footer 2
        </li>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Footer 3"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Footer 3
        </li>
      </ul>
      <ul id="columns" class>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Columns 1"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Columns 1
        </li>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Columns 2"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Columns 2
        </li>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Columns 3"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Columns 3
        </li>
      </ul>
      <ul id="tabs" class>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Tabs 1"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Tabs 1
        </li>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Tabs 2"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Tabs 2
        </li>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Tabs 3"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Tabs 3
        </li>
      </ul>
      <ul id="offers" class>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Offers 1"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Offers 1
        </li>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Offers 2"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Offers 2
        </li>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Offers 3"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Offers 3
        </li>
      </ul>
      <ul id="cards" class>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Cards 1"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Cards 1
        </li>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Cards 2"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Cards 2
        </li>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Cards 3"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Cards 3
        </li>
      </ul>
      <ul id="social" class>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Social 1"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Social 1
        </li>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Social 2"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Social 2
        </li>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Social 3"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Social 3
        </li>
      </ul>
      <ul id="bling" class>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Bling 1"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Bling 1
        </li>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Bling 2"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Bling 2
        </li>
        <li
          title="Click and drag module into page builder"
          data-module-name="SR Bling 3"
        >
          <img src="/images/sr-testimonial-1.png" />
          SR Bling 3
        </li>
      </ul>
    </div>
  </nav>
</template>

<script>
import JQuery from "jquery";
let $ = JQuery;

export default {
  components: {},

  data: () => ({
    appName: window.config.appName,
    isHover: false,
    isClickSearch: false,
    isHoverNavigation: false,
    isHoverHero: false,
    isHoverFooter: false,
    isHoverColumns: false,
    isHoverTabs: false,
    isHoverOffers: false,
    isHoverCards: false,
    isHoverSocial: false,
    isHoverBling: false
  }),

  methods: {
    clickOpenSearch: function() {
      this.isHoverNavigation = false;
      this.isHoverHero = false;
      this.isHoverFooter = false;
      this.isHoverColumns = false;
      this.isHoverTabs = false;
      this.isHoverOffers = false;
      this.isHoverCards = false;
      this.isHoverSocial = false;
      this.isHoverBling = false;
      this.isClickSearch = true;
      this.isHover = true;
    },

    mouseOver: function(menuName) {
      this.isClickSearch = false;
      this.isHoverNavigation = false;
      this.isHoverHero = false;
      this.isHoverFooter = false;
      this.isHoverColumns = false;
      this.isHoverTabs = false;
      this.isHoverOffers = false;
      this.isHoverCards = false;
      this.isHoverSocial = false;
      this.isHoverBling = false;

      this.isHover = true;

      if (menuName == "navigation") this.isHoverNavigation = true;
      if (menuName == "hero") this.isHoverHero = true;
      if (menuName == "footer") this.isHoverFooter = true;
      if (menuName == "columns") this.isHoverColumns = true;
      if (menuName == "tabs") this.isHoverTabs = true;
      if (menuName == "offers") this.isHoverOffers = true;
      if (menuName == "cards") this.isHoverCards = true;
      if (menuName == "social") this.isHoverSocial = true;
      if (menuName == "bling") this.isHoverBling = true;
    },

    mouseOut: function() {
      this.isHoverNavigation = false;
      this.isHoverHero = false;
      this.isHoverFooter = false;
      this.isHoverColumns = false;
      this.isHoverTabs = false;
      this.isHoverOffers = false;
      this.isHoverCards = false;
      this.isHoverSocial = false;
      this.isHoverBling = false;
      this.isClickSearch = false;
      this.isHover = false;
    }
  },

  mounted: function() {}
};
</script>
