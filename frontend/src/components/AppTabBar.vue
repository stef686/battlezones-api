<script setup lang="ts">
/**
 * The bottom tab bar, and the only navigation a Player needs mid-event.
 *
 * A phone held in one hand at a venue is the case this is built for: the four
 * destinations a Player actually returns to during a round sit under the
 * thumb, and everything else is reached from the Event screen. It is deliberately
 * fixed to the bottom rather than the top for the same reason.
 *
 * Destinations are the same for everybody. Which screens a viewer may act on
 * is the API's answer, not this component's: showing a tab that leads to a
 * screen saying "you are not entered" is better than a bar whose shape changes
 * under a Player between rounds.
 */
import { CalendarDaysIcon, ChartBarIcon, HomeIcon, PuzzlePieceIcon } from '@heroicons/vue/24/outline';
import type { FunctionalComponent } from 'vue';
import { RouterLink } from 'vue-router';

defineProps<{ eventSlug: string }>();

interface Tab {
    name: string;
    label: string;
    testid: string;
    icon: FunctionalComponent;
}

const tabs: Tab[] = [
    {
        name: 'event',
        label: 'Event',
        testid: 'tab-event',
        icon: HomeIcon,
    },
    {
        name: 'schedule',
        label: 'Schedule',
        testid: 'tab-schedule',
        icon: CalendarDaysIcon,
    },
    {
        name: 'my-game',
        label: 'My game',
        testid: 'tab-my-game',
        icon: PuzzlePieceIcon,
    },
    {
        name: 'standings',
        label: 'Standings',
        testid: 'tab-standings',
        icon: ChartBarIcon,
    },
];
</script>

<template>
  <nav
    data-testid="tab-bar"
    aria-label="Event"
    class="fixed inset-x-0 bottom-0 z-40 border-t border-navbar-line bg-navbar pb-[env(safe-area-inset-bottom)] md:hidden"
  >
    <ul class="mx-auto flex w-full max-w-md items-stretch">
      <li
        v-for="tab in tabs"
        :key="tab.name"
        class="flex-1"
      >
        <!-- Exact, not inclusive: every Event screen sits under the Event
             path, so an inclusive match would light the Event tab on all four
             of them. None of the four nest inside another. -->
        <RouterLink
          :to="{ name: tab.name, params: { eventSlug } }"
          :data-testid="tab.testid"
          class="flex flex-col items-center gap-1 px-1 py-2.5 text-[11px] font-medium text-muted-foreground-1 hover:text-navbar-nav-foreground focus:text-navbar-nav-foreground focus:outline-hidden"
          exact-active-class="text-primary"
        >
          <component
            :is="tab.icon"
            class="size-5 shrink-0"
          />
          {{ tab.label }}
        </RouterLink>
      </li>
    </ul>
  </nav>
</template>
