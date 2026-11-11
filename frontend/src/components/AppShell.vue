<script setup lang="ts">
/**
 * The chrome every screen is drawn inside.
 *
 * One job, and it is not navigation policy: put the Event header and the
 * Event nav on the screens that belong to an Event. The tab bar is the app's
 * global nav and shows on every screen the shell is drawn around. Which routes an unclaimed or
 * signed-out viewer may reach is settled once in the router guard, and this
 * component does not second-guess it — it only reflects the route it has been
 * given.
 *
 * The header carries the safe-area inset itself rather than taking it from
 * here, so the Event's surface runs under the status bar on a notched device
 * and only the type clears it.
 *
 * An Event's screens wear its Event Colour: the header, the nav and the screen
 * share one scope that swaps the primary ramp for the Event's own. The tab bar
 * stays outside it, because it is the app's chrome rather than the Event's.
 * The Event is read from the same cached query the header reads.
 */
import { useQuery } from '@tanstack/vue-query';
import { computed } from 'vue';
import { useRoute } from 'vue-router';

import { useApiClient } from '@/api';
import { fetchEvent } from '@/api/events';
import { keys } from '@/api/keys';
import AppTabBar from '@/components/AppTabBar.vue';
import EventHeader from '@/components/EventHeader.vue';
import EventNav from '@/components/EventNav.vue';
import { eventColourStyle } from '@/lib/event-colour';

const route = useRoute();
const client = useApiClient();

const eventSlug = computed(() => {
    const slug = route.params.eventSlug;

    return typeof slug === 'string' && slug.length > 0 ? slug : null;
});

const { data: event } = useQuery({
    queryKey: computed(() => keys.event(eventSlug.value ?? '')),
    queryFn: () => fetchEvent(client, eventSlug.value ?? ''),
    enabled: computed(() => eventSlug.value !== null),
    retry: false,
});

const colourStyle = computed(() => eventColourStyle(eventSlug.value === null ? null : event.value?.colour ?? null));
</script>

<template>
  <div class="flex min-h-screen flex-col bg-background-2">
    <div
      data-testid="event-colour-scope"
      class="flex flex-1 flex-col"
      :style="colourStyle"
    >
      <EventHeader
        v-if="eventSlug"
        :event-slug="eventSlug"
      />

      <EventNav
        v-if="eventSlug"
        :event-slug="eventSlug"
      />

      <!-- The tab bar is fixed, so the last card on a long screen needs room to
           clear it rather than sitting underneath. -->
      <div class="flex-1 pb-20">
        <slot />
      </div>
    </div>

    <AppTabBar />
  </div>
</template>
