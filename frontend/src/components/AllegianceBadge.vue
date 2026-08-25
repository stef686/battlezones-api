<script setup lang="ts">
import { computed } from 'vue';

/**
 * Which side a party fights for, legible at arm's length.
 *
 * Colour and word together: colour alone fails a Player who cannot tell these
 * two hues apart, and the word alone fails everyone scanning a list in a dim
 * hall.
 */
const props = defineProps<{ allegiance: string | null }>();

const known = computed(() => props.allegiance === 'loyalist' || props.allegiance === 'traitor');

const label = computed(() => {
  switch (props.allegiance) {
    case 'loyalist':
      return 'Loyalist';
    case 'traitor':
      return 'Traitor';
    default:
      return 'Undeclared';
  }
});

const classes = computed(() => {
  switch (props.allegiance) {
    case 'loyalist':
      return 'bg-loyalist text-loyalist-foreground';
    case 'traitor':
      return 'bg-traitor text-traitor-foreground';
    default:
      return 'border border-border text-muted-foreground-1';
  }
});
</script>

<template>
  <span
    :data-testid="`allegiance-${allegiance ?? 'none'}`"
    :data-known="known"
    class="inline-flex shrink-0 items-center gap-x-1.5 rounded-full px-3 py-1.5 text-xs font-medium uppercase tracking-wide"
    :class="classes"
  >
    {{ label }}
  </span>
</template>
