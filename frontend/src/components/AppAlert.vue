<script setup lang="ts">
/**
 * A short outcome, stated in colour.
 *
 * `error` is announced assertively because it is the answer to something the
 * reader just did and they are waiting for it; the other tones are polite,
 * so a Player mid-form is not interrupted by a note that merely confirms.
 */
import { computed } from 'vue';

const props = withDefaults(defineProps<{ tone?: 'success' | 'error' | 'info' }>(), { tone: 'info' });

const TONES = {
  success: 'border-success/30 text-success',
  error: 'border-destructive/40 text-destructive',
  info: 'border-border text-muted-foreground-1',
} as const;

const classes = computed(() => ['rounded-lg border p-4 text-sm', TONES[props.tone]]);

const role = computed(() => (props.tone === 'error' ? 'alert' : 'status'));
</script>

<template>
  <div
    :role="role"
    :class="classes"
  >
    <slot />
  </div>
</template>
