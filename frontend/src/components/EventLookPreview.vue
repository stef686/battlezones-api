<script setup lang="ts">
/**
 * An Event's header as it will look, before the Event exists: the Banner (or
 * the flat surface) with the Event Colour's scale applied, and the start of
 * the Event nav beneath it, whose lit tab is where the colour shows first.
 *
 * Draws `EventHeaderSurface`, the header's own markup, inside the same colour
 * scope `AppShell` puts an Event's screens in, so what is previewed is what
 * will be drawn. Decorative to a screen reader: everything in it is a choice
 * already named by the fields around it.
 */
import { computed } from 'vue';

import EventHeaderSurface from '@/components/EventHeaderSurface.vue';
import { eventColourStyle } from '@/lib/event-colour';

const props = withDefaults(defineProps<{
  name: string;
  gameSystem?: string | null;
  dates?: string;
  banner?: { large: string; small: string } | null;
  colour: string | null;
}>(), {
  gameSystem: null,
  dates: '',
  banner: null,
});

const style = computed(() => eventColourStyle(props.colour));

/** The first of the Event nav's tabs, Home lit, as an Event's own screen opens. */
const tabs = ['Home', 'Rounds', 'Standings', 'Attendees'];
</script>

<template>
  <div
    aria-hidden="true"
    class="overflow-hidden rounded-lg border border-card-line"
    :style="style"
  >
    <EventHeaderSurface
      :name="name === '' ? 'Your event' : name"
      :game-system="gameSystem"
      :dates="dates"
      :banner="banner"
    />

    <ul class="flex gap-2 border-t border-navbar-line bg-navbar px-4">
      <li
        v-for="(tab, index) in tabs"
        :key="tab"
        class="border-b-2 px-2 py-3 text-sm font-medium whitespace-nowrap"
        :class="index === 0 ? 'border-primary text-navbar-nav-foreground' : 'border-transparent text-muted-foreground-1'"
      >
        {{ tab }}
      </li>
    </ul>
  </div>
</template>
