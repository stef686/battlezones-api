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
import { EVENT_SECTIONS, eventNavChipClass } from '@/lib/event-nav';

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

/** The first of the Event nav's sections, Home lit, as an Event's own screen opens. */
const sections = EVENT_SECTIONS.slice(0, 4);
</script>

<template>
  <div
    aria-hidden="true"
    class="overflow-hidden rounded-lg border border-card-line"
    :style="style"
  >
    <EventHeaderSurface
      :name="name === '' ? 'Your event' : name"
      :game-system="gameSystem ?? 'Game system'"
      :dates="dates"
      :banner="banner"
    />

    <ul class="flex gap-2 border-t border-navbar-line bg-navbar px-4">
      <li
        v-for="(section, index) in sections"
        :key="section.name"
        :class="eventNavChipClass(index === 0)"
      >
        {{ section.label }}
      </li>
    </ul>
  </div>
</template>
