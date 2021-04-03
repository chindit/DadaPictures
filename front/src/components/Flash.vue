<template>
  <div
    class="alert"
    :class="'alert-' + message.level"
    v-if="!hidden"
  >
    {{ $t(message.message) }}
  </div>
</template>

<script lang="ts">
import { ref } from 'vue';
import { useStore } from 'vuex';
import { defineComponent } from '@vue/composition-api';

export default defineComponent({
  props: {
    message: {
      required: true,
    },
  },
  setup(props) {
    const hidden = ref(false);
    const store = useStore();

    setTimeout(() => {
      store.commit('deleteFlash', props.message.id);
    }, 10000);

    return {
      hidden,
    };
  },
});
</script>

<style lang="sass" scoped>
.hidden
  display: none
</style>
