<script setup lang="ts">
/**
 * The header every Event screen opens with.
 *
 * Nothing is drawn until the Event is read: an Event nobody may see answers
 * 404, and a header naming it blank above that screen's own "not found" reads
 * as a bug rather than as an answer.
 *
 * It names the Event once, at the top of all thirteen of its screens, so a
 * Player who deep-linked into Standings knows whose Standings they are. It
 * scrolls away with the page: mid-round the pairings matter more than the
 * name of the Event a Player is already standing in.
 *
 * The surface behind the type is flat here and carries a Banner later, so the
 * overlay text sits on scrims rather than on the surface. That way it has one
 * colour rule in every state — a pale Banner cannot make it unreadable, and
 * muting a dark one is the accepted cost.
 *
 * The safe-area inset is the header's own: the background runs under the
 * status bar, and only the type clears it.
 */
import { useQuery } from '@tanstack/vue-query';
import { computed } from 'vue';

import { useApiClient } from '@/api';
import { fetchEvent } from '@/api/events';
import { keys } from '@/api/keys';
import { formatDateRange } from '@/lib/dates';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();

const { data: event } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

const dates = computed(() => formatDateRange(event.value?.starts_at ?? null, event.value?.ends_at ?? null));
</script>

<template>
  <header
    v-if="event"
    data-testid="event-header"
    class="relative isolate w-full bg-navbar pt-[env(safe-area-inset-top)]"
  >
    <!-- Light status-bar text reads over the top of the header whatever is
         behind it, so the scrim is here before there is ever an image. -->
    <div
      aria-hidden="true"
      class="pointer-events-none absolute inset-x-0 top-0 h-20 event-header-scrim-top"
    />

    <div class="relative flex h-40 flex-col justify-end">
      <div
        aria-hidden="true"
        class="pointer-events-none absolute inset-x-0 bottom-0 h-28 event-header-scrim-bottom"
      />

      <div class="relative mx-auto flex w-full max-w-md flex-col px-5 pb-4 leading-tight">
        <p
          v-if="event?.game_system"
          data-testid="event-header-game-system"
          class="text-xs font-medium uppercase tracking-wide text-event-header-foreground-muted"
        >
          {{ event.game_system.name }}
        </p>

        <h1
          data-testid="event-header-name"
          class="text-2xl font-semibold text-event-header-foreground"
        >
          {{ event.name }}
        </h1>

        <p
          v-if="dates"
          data-testid="event-header-dates"
          class="text-sm text-event-header-foreground-muted"
        >
          {{ dates }}
        </p>
      </div>
    </div>
  </header>
</template>
