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
 */
import { computed } from 'vue';
import { useRoute } from 'vue-router';

import AppTabBar from '@/components/AppTabBar.vue';
import EventHeader from '@/components/EventHeader.vue';
import EventNav from '@/components/EventNav.vue';

const route = useRoute();

const eventSlug = computed(() => {
    const slug = route.params.eventSlug;

    return typeof slug === 'string' && slug.length > 0 ? slug : null;
});
</script>

<template>
  <div class="flex min-h-screen flex-col bg-background-2">
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
    <div class="flex-1 pb-20 md:pb-0">
      <slot />
    </div>

    <AppTabBar />
  </div>
</template>
