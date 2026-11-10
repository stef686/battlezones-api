<script setup lang="ts">
/**
 * Where a toast is said: floated clear of the tab bar, as the update banner is.
 *
 * The live region is always in the page and only its message comes and goes,
 * because a screen reader announces a change inside a region it already knows
 * about, not a region that arrives with its text already in it.
 */
import { storeToRefs } from 'pinia';

import { useToastStore } from '@/stores/toast';

const { message } = storeToRefs(useToastStore());
</script>

<template>
  <div
    role="status"
    data-testid="toast-region"
    class="pointer-events-none fixed inset-x-4 bottom-24 z-50 mx-auto max-w-md md:bottom-6"
  >
    <p
      v-if="message"
      data-testid="toast"
      class="rounded-xl border border-border bg-card px-4 py-3 text-sm text-foreground shadow-lg"
    >
      {{ message }}
    </p>
  </div>
</template>
