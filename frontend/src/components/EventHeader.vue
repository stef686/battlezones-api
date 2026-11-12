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
 * What it looks like is `EventHeaderSurface`, shared with the create wizard's
 * preview. The safe-area inset is the header's own: the background runs under
 * the status bar, and only the type clears it.
 *
 * A Draft or Cancelled Event is read only by its Organisers, so the notice
 * that says so needs no viewer check: anyone else never gets this far. It is
 * amber, never the Event Colour, because it is about the Event's state and an
 * Organiser's own colour could make it vanish into the header.
 */
import { useQuery } from '@tanstack/vue-query';
import { computed } from 'vue';

import { useApiClient } from '@/api';
import { fetchEvent } from '@/api/events';
import { keys } from '@/api/keys';
import EventHeaderSurface from '@/components/EventHeaderSurface.vue';
import { formatDateRange } from '@/lib/dates';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();

const { data: event } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

const hiddenNotices: Record<string, string> = {
  draft: 'Draft — only Organisers can see this event',
  cancelled: 'Cancelled — only Organisers can see this event',
};

const visibilityNotice = computed(() => (event.value ? hiddenNotices[event.value.status] ?? null : null));

const dates = computed(() => formatDateRange(event.value?.starts_at ?? null, event.value?.ends_at ?? null));
</script>

<template>
  <EventHeaderSurface
    v-if="event"
    tag="header"
    inset-top
    data-testid="event-header"
    :name="event.name"
    :game-system="event.game_system?.name ?? null"
    :dates="dates"
    :banner="event.banner"
  >
    <template
      v-if="visibilityNotice"
      #notice
    >
      <p
        data-testid="event-header-visibility"
        class="mb-2 self-start rounded-full border border-warning/40 bg-background-2/80 px-2.5 py-0.5 text-xs font-medium text-warning"
      >
        {{ visibilityNotice }}
      </p>
    </template>
  </EventHeaderSurface>
</template>
