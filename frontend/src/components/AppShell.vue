<script setup lang="ts">
/**
 * The chrome every screen is drawn inside.
 *
 * One job, and it is not navigation policy: put the tab bar on the screens
 * that belong to an Event. Which routes an unclaimed or signed-out viewer may
 * reach is settled once in the router guard, and this component does not
 * second-guess it — it only reflects the route it has been given.
 *
 * There is no top bar: every screen names itself, so a header naming the Event
 * a second time only cost vertical space on a phone.
 */
import { computed } from 'vue';
import { useRoute } from 'vue-router';

import AppTabBar from '@/components/AppTabBar.vue';

const route = useRoute();

const eventSlug = computed(() => {
    const slug = route.params.eventSlug;

    return typeof slug === 'string' && slug.length > 0 ? slug : null;
});
</script>

<template>
  <div class="flex min-h-screen flex-col bg-background-2 pt-[env(safe-area-inset-top)]">
    <!-- The tab bar is fixed, so the last card on a long screen needs room to
         clear it rather than sitting underneath. -->
    <div :class="['flex-1', eventSlug ? 'pb-20 md:pb-0' : '']">
      <slot />
    </div>

    <AppTabBar
      v-if="eventSlug"
      :event-slug="eventSlug"
    />
  </div>
</template>
