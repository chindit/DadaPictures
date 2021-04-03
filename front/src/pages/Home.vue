<template>
  <div class="home">
    <img alt="Vue logo" src="../assets/logo.png">
  </div>
</template>

<script>
import Api from '@/Api';

export default {
  name: 'Home',
  data() {
    return {
      page: 1,
      packs: [],
    };
  },
  methods: {
    async loadPacks() {
      await Api.get(
        `/packs?page=${this.page}&XDEBUG_SESSION_START=1`,
      );
    },
  },
  mounted() {
    if (this.packs.length === 0) {
      this.loadPacks();
    }
  },
};
</script>
