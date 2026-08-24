<script setup lang="ts">
import { onMounted } from 'vue';
import { RouterView } from 'vue-router';

import ClaimPrompt from '@/components/ClaimPrompt.vue';
import UpdateBanner from '@/components/UpdateBanner.vue';
import { useServiceWorker } from '@/composables/useServiceWorker';

const { updateAvailable, register, applyUpdate } = useServiceWorker();

onMounted(() => {
  void register();
});
</script>

<template>
  <ClaimPrompt />
  <RouterView />
  <UpdateBanner
    :available="updateAvailable"
    @reload="applyUpdate"
  />
</template>
