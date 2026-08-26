<script setup lang="ts">
/**
 * The Event's own sections, pinned under the header.
 *
 * A Player mid-round moves between Home, Rounds, Standings, Attendees and
 * Schedule constantly, and hunting for the browser's back button between each
 * is a tap they should not have to make. The strip pins as the header scrolls
 * away so those five are reachable from anywhere inside the Event — Home
 * first, because a Player who has lost their place wants the Event screen.
 *
 * The lit section is marked with an underline rather than a filled pill: six
 * pills read as six buttons competing with the screen's own calls to action,
 * where a tab strip reads as where you are.
 *
 * Six tabs do not fit a phone viewport, so the strip scrolls horizontally —
 * natively, because a custom drag handler on a bar this small fights the
 * browser's own momentum and gets it wrong.
 */
import { useQuery } from '@tanstack/vue-query';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { RouterLink, useRoute } from 'vue-router';

import { useApiClient } from '@/api';
import { fetchEvent } from '@/api/events';
import { keys } from '@/api/keys';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();
const route = useRoute();

const { data: event, error } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

/**
 * Which chip a screen belongs to, by route name rather than by matching the
 * URL: a Round's detail screen belongs to Rounds and an Attendee's to
 * Attendees, while the Poll and My game screens belong to no chip at all.
 * Kept beside the chip list so the whole relationship reads in one place.
 */
const chipOfRoute: Record<string, string> = {
  event: 'event',
  rounds: 'rounds',
  round: 'rounds',
  standings: 'standings',
  attendees: 'attendees',
  attendee: 'attendees',
  schedule: 'schedule',
  'my-team': 'my-team',
};

const sections = [
  { name: 'event', label: 'Home' },
  { name: 'rounds', label: 'Rounds' },
  { name: 'standings', label: 'Standings' },
  { name: 'attendees', label: 'Attendees' },
  { name: 'schedule', label: 'Schedule' },
];

/**
 * An Event nobody may see answers 404, and a nav offering its sections above
 * that screen's own "not found" would be five dead ends. It stays through the
 * load, though: the five sections it lists do not depend on the Event.
 */
const readable = computed(() => error.value === null);

const list = ref<HTMLElement | null>(null);

/**
 * The lit chip is scrolled into view rather than left wherever the strip
 * happens to sit: the tabs do not fit a phone, so a Player arriving on
 * Standings from a deep link would otherwise see a nav with nothing selected.
 */
async function showActiveChip(): Promise<void> {
  await nextTick();

  list.value?.querySelector('[aria-current="page"]')?.scrollIntoView({ block: 'nearest', inline: 'nearest' });
}

onMounted(showActiveChip);

watch(() => route.name, () => {
  void showActiveChip();
});

const active = computed(() => chipOfRoute[String(route.name)] ?? null);

/**
 * My team is the one chip allowed to be absent, and it is last for exactly
 * that reason: a viewer who has not entered has no team, and a trailing chip
 * can go without moving the five in front of it.
 */
const chips = computed(() => event.value?.viewer?.is_attendee === true
  ? [...sections, { name: 'my-team', label: 'My team' }]
  : sections);
</script>

<template>
  <nav
    v-if="readable"
    data-testid="event-nav"
    aria-label="Event sections"
    class="sticky top-0 z-30 border-b border-navbar-line bg-navbar event-nav-fade"
  >
    <ul
      ref="list"
      class="mx-auto flex w-full max-w-md gap-2 overflow-x-auto px-4 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
    >
      <li
        v-for="chip in chips"
        :key="chip.name"
        class="shrink-0"
      >
        <RouterLink
          :to="{ name: chip.name, params: { eventSlug } }"
          :data-testid="`event-nav-${chip.name}`"
          :aria-current="active === chip.name ? 'page' : undefined"
          :class="[
            'block border-b-2 px-2 py-3 text-sm font-medium whitespace-nowrap focus:outline-hidden',
            active === chip.name
              ? 'border-primary text-navbar-nav-foreground'
              : 'border-transparent text-muted-foreground-1 hover:text-navbar-nav-foreground focus:text-navbar-nav-foreground',
          ]"
        >
          {{ chip.label }}
        </RouterLink>
      </li>
    </ul>
  </nav>
</template>
