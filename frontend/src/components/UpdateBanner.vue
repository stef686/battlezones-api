<script setup lang="ts">
import { ref, watch } from 'vue';

/**
 * The offer of a new version, never the taking of it.
 *
 * A fix deployed on the Saturday morning has to reach the room, but a Player
 * halfway through typing a result must not have the page reloaded under them.
 * So this asks, and the reader decides — and can wave it away and be asked
 * again by the next version.
 */
const props = defineProps<{ available: boolean }>();

const emit = defineEmits<{ reload: [] }>();

const dismissed = ref(false);

watch(() => props.available, (offered) => {
  if (offered) {
    dismissed.value = false;
  }
});
</script>

<template>
  <!-- Floated clear of the tab bar rather than laid over it: a banner that
       covers the navigation traps a reader who wants neither option. -->
  <div
    v-if="props.available && !dismissed"
    data-testid="update-available"
    role="status"
    class="fixed inset-x-4 bottom-24 z-50 mx-auto flex max-w-md items-center gap-3 rounded-xl border border-border bg-card px-4 py-3 shadow-lg md:bottom-6"
  >
    <p class="min-w-0 flex-1 text-sm text-foreground">
      There is a new version of the app.
    </p>
    <button
      type="button"
      data-testid="take-update"
      class="inline-flex shrink-0 items-center rounded-lg border border-transparent bg-primary px-3 py-2 text-sm font-medium text-primary-foreground hover:bg-primary-hover focus:bg-primary-focus focus:outline-hidden"
      @click="emit('reload')"
    >
      Reload
    </button>
    <button
      type="button"
      data-testid="dismiss-update"
      class="inline-flex shrink-0 items-center rounded-lg border border-transparent px-2 py-2 text-sm font-medium text-muted-foreground-1 hover:bg-muted-hover focus:bg-muted-hover focus:outline-hidden"
      @click="dismissed = true"
    >
      Later
    </button>
  </div>
</template>
