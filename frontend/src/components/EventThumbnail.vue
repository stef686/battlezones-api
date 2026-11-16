<script setup lang="ts">
/**
 * A small square standing for an Event in a list, so a row is recognisable
 * before its name is read: the Banner's small size when the Event has one,
 * otherwise a square of the Event Colour.
 *
 * The square is `bg-primary` inside the same colour scope `AppShell` puts an
 * Event's screens in, so it is the colour the Event's own screens wear rather
 * than the raw hex; an Event without a colour gets an empty scope and the
 * platform colour shows through.
 *
 * Decorative to a screen reader: the Event's name sits beside it in the row,
 * so anything said here would say it twice.
 */
import { computed } from 'vue';

import { eventColourStyle } from '@/lib/event-colour';

const props = withDefaults(defineProps<{
  banner?: { large: string; small: string } | null;
  colour?: string | null;
}>(), {
  banner: null,
  colour: null,
});

const style = computed(() => eventColourStyle(props.colour));
</script>

<template>
  <span
    aria-hidden="true"
    class="size-12 shrink-0 overflow-hidden rounded-md bg-primary"
    :style="style"
  >
    <img
      v-if="banner"
      :src="banner.small"
      alt=""
      class="size-full object-cover"
    >
  </span>
</template>
