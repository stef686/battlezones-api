<script setup lang="ts">
/**
 * A pill naming a status the reader has to be told about: a Draft or a
 * Cancelled Event, which only its Organisers can see. Drawn in the warning
 * tone because both are something to act on, not information.
 *
 * Classes passed in merge onto the pill, so a placement (the Event header's
 * margin and its backing over a Banner) stays with the caller.
 */
withDefaults(defineProps<{ as?: 'span' | 'p' }>(), { as: 'span' });
</script>

<template>
  <component
    :is="as"
    class="inline-flex shrink-0 items-center rounded-full border border-warning/40 px-2.5 py-0.5 text-xs font-medium text-warning"
  >
    <slot />
  </component>
</template>
