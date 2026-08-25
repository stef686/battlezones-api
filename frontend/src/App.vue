<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { RouterView, useRoute } from 'vue-router';

import AppShell from '@/components/AppShell.vue';
import ClaimPrompt from '@/components/ClaimPrompt.vue';
import UpdateBanner from '@/components/UpdateBanner.vue';
import { useServiceWorker } from '@/composables/useServiceWorker';

const { updateAvailable, register, applyUpdate } = useServiceWorker();

const route = useRoute();

/**
 * Chrome is the default, and a route opts out. A screen reached with a token
 * in the URL has no Event to head and, until it is used, no session to name.
 */
const chrome = computed(() => route.meta.chrome !== false);

onMounted(() => {
  void register();
});
</script>

<template>
  <ClaimPrompt />

  <AppShell v-if="chrome">
    <RouterView />
  </AppShell>
  <RouterView v-else />

  <UpdateBanner
    :available="updateAvailable"
    @reload="applyUpdate"
  />
</template>
