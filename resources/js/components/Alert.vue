<template>
  <div v-if="myShow" class="sticky-drawer-alert">
    <!-- alerts -->
    <!-- Module Already exisits alert -->
    <div v-if="myOptions.type == 'danger'" class="alert alert-danger" role="alert">
      {{ myOptions.content }}
      <a
        v-if="appEnv == 'local'"
        href="/api/debug"
        target="_blank"
      >View Detail</a>
      <button type="button" class="close" aria-label="Close" @click="close()">
        <img src="/images/icon-close-white.svg" />
      </button>
    </div>
    <!-- Warning alert -->
    <div v-if="myOptions.type == 'warning'" class="alert alert-warning" role="alert">
      {{ myOptions.content }}
      <a
        v-if="appEnv == 'local'"
        href="/api/debug"
        target="_blank"
      >View Detail</a>
      <button type="button" class="close" aria-label="Close" @click="close()">
        <img src="/images/icon-close-white.svg" />
      </button>
    </div>
    <!-- Success alert -->
    <div v-if="myOptions.type == 'success'" class="alert alert-success" role="alert">
      {{ myOptions.content }}
      <a
        v-if="appEnv == 'local'"
        href="/api/debug"
        target="_blank"
      >View Detail</a>
      <button type="button" class="close" aria-label="Close" @click="close()">
        <img src="/images/icon-close-white.svg" />
      </button>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      appEnv: window.config.appEnv,
      myOptions: this.options,
      myShow: this.show
    };
  },
  props: {
    options: {
      type: Object,
      default: {}
    },
    show: {
      type: Boolean,
      default: false
    }
  },
  computed: {},
  methods: {
    close() {
      this.$emit("close"); // should to emit to change parent components status
      this.myOptions = {};
    }
  },
  watch: {
    options() {
      this.myOptions = this.options;
    },
    show(val) {
      this.myShow = val;
    }
  }
};
</script>