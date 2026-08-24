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
  <div
    v-if="props.available && !dismissed"
    data-testid="update-available"
    class="fixed inset-x-0 bottom-0 z-50 flex items-center gap-3 border-t border-border bg-surface-raised px-5 py-4"
  >
    <p class="min-w-0 flex-1 text-sm text-ink">
      There is a new version of the app.
    </p>
    <button
      type="button"
      data-testid="take-update"
      class="rounded-lg bg-accent px-3 py-2 text-sm font-semibold text-accent-ink"
      @click="emit('reload')"
    >
      Reload
    </button>
    <button
      type="button"
      data-testid="dismiss-update"
      class="rounded-lg px-2 py-2 text-sm text-ink-muted"
      @click="dismissed = true"
    >
      Later
    </button>
  </div>
</template>
