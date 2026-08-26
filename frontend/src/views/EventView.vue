<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query';
import { computed } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchEvent } from '@/api/events';
import { keys } from '@/api/keys';
import { fetchPolls } from '@/api/polls';
import type { Game } from '@/api/results';
import MissingNotice from '@/components/MissingNotice.vue';
import { formatDateRange } from '@/lib/dates';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();

const { data: event, isPending, error } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

/**
 * An Event nobody may see answers 404, exactly as one that does not exist
 * does. The screen must not distinguish them either.
 */
const missing = computed(() => error.value instanceof ApiError && error.value.kind === 'not_found');

/**
 * Polls are read here only to answer one question: is there a vote this
 * reader can cast right now. A Player who has to go looking for the window
 * misses it, and the window is the whole point of a Poll being open.
 */
const { data: polls } = useQuery({
  queryKey: computed(() => keys.polls(props.eventSlug)),
  queryFn: () => fetchPolls(client, props.eventSlug),
  enabled: computed(() => event.value?.viewer !== null && event.value?.viewer !== undefined),
  retry: false,
});

const openToMe = computed(() => (polls.value ?? []).find((poll) => poll.is_open_for_me === true) ?? null);

const dates = computed(() => formatDateRange(event.value?.starts_at ?? null, event.value?.ends_at ?? null));

const venue = computed(() => {
  const place = event.value?.venue;

  if (place === undefined) {
    return [];
  }

  return [place.name, place.address, place.city, place.country].filter((line): line is string => Boolean(line));
});

const places = computed(() => {
  const loaded = event.value;

  if (loaded === undefined || loaded.max_attendees === null || loaded.attendees_count === undefined) {
    return null;
  }

  return `${loaded.attendees_count} of ${loaded.max_attendees} places taken`;
});

const viewer = computed(() => event.value?.viewer ?? null);

/**
 * The table this Player is on right now.
 *
 * My game is not a nav chip — it only means anything while a Round is live —
 * so it is a call-to-action here instead, in the same idiom as the open vote.
 * Read only for a viewer who has entered, exactly as the Polls query is
 * gated on there being a viewer at all: nobody else has a game to be on.
 */
const { data: myGame } = useQuery({
  queryKey: computed(() => keys.myGame(props.eventSlug)),
  queryFn: () => client.get<{ data: Game | null }>(`/api/events/${props.eventSlug}/my-game`),
  enabled: computed(() => event.value?.viewer?.is_attendee === true),
  retry: false,
});

const liveGame = computed(() => myGame.value?.data ?? null);
</script>

<template>
  <main class="mx-auto flex w-full max-w-md flex-col gap-6 p-5">
    <p
      v-if="isPending"
      class="text-muted-foreground-1"
    >
      Loading the event…
    </p>

    <MissingNotice
      v-else-if="missing"
      thing="event"
    />

    <p
      v-else-if="error"
      data-testid="event-error"
      role="alert"
      class="text-destructive"
    >
      {{ (error as ApiError).message }}
    </p>

    <template v-else-if="event">
      <header class="flex flex-col gap-2">
        <p
          v-if="event.game_system"
          class="text-xs font-medium uppercase tracking-widest text-muted-foreground"
        >
          {{ event.game_system.name }}
        </p>
        <h1
          data-testid="event-name"
          class="text-2xl font-bold tracking-tight text-foreground"
        >
          {{ event.name }}
        </h1>
        <p
          v-if="dates"
          data-testid="event-dates"
          class="text-sm text-muted-foreground-1"
        >
          {{ dates }}
        </p>
        <p
          v-if="places"
          data-testid="event-places"
          class="text-sm text-muted-foreground"
        >
          {{ places }}
        </p>
      </header>

      <p
        v-if="event.description"
        data-testid="event-description"
        class="whitespace-pre-line text-sm text-muted-foreground-1"
      >
        {{ event.description }}
      </p>

      <!-- The one Poll fact worth interrupting for: there is a window, and it
           is open to you now. It sits above the navigation because a Player
           who has to scroll to find it has already missed it. -->
      <RouterLink
        v-if="openToMe"
        :to="{ name: 'poll', params: { eventSlug: props.eventSlug, pollId: openToMe.id } }"
        data-testid="voting-open"
        class="inline-flex items-center justify-center gap-x-2 rounded-lg border border-transparent bg-primary px-4 py-3 text-sm font-medium text-primary-foreground hover:bg-primary-hover focus:bg-primary-focus focus:outline-hidden"
      >
        Voting is open: {{ openToMe.name }}
      </RouterLink>

      <!-- Mid-round, the table and the opponent are what a Player is looking
           for, so this sits with the other conditional call-to-action rather
           than in the nav: there is nothing to link to between Rounds. -->
      <RouterLink
        v-if="liveGame"
        :to="{ name: 'my-game', params: { eventSlug: props.eventSlug } }"
        data-testid="my-game-link"
        class="inline-flex items-center justify-center gap-x-2 rounded-lg border border-transparent bg-primary px-4 py-3 text-sm font-medium text-primary-foreground hover:bg-primary-hover focus:bg-primary-focus focus:outline-hidden"
      >
        Your game{{ liveGame.table_number === null ? '' : `: table ${liveGame.table_number}` }}
      </RouterLink>

      <RouterLink
        v-if="viewer?.is_attendee"
        :to="{ name: 'my-team', params: { eventSlug: props.eventSlug } }"
        data-testid="my-team-link"
        class="inline-flex items-center justify-center gap-x-2 rounded-lg border border-border bg-card px-4 py-3 text-sm font-medium text-foreground hover:bg-muted-hover focus:bg-muted-hover focus:outline-hidden"
      >
        Your team
      </RouterLink>
      <RouterLink
        v-else-if="viewer?.permissions.register"
        :to="{ name: 'register', params: { eventSlug: props.eventSlug } }"
        data-testid="register-link"
        class="inline-flex items-center justify-center gap-x-2 rounded-lg border border-transparent bg-primary px-4 py-3 text-sm font-medium text-primary-foreground hover:bg-primary-hover focus:bg-primary-focus focus:outline-hidden"
      >
        Enter this event
      </RouterLink>

      <!-- Organiser controls exist only where the viewer context grants them,
           never hidden by CSS: an unauthorised reader is not sent them. -->
      <section
        v-if="viewer?.permissions.organise"
        data-testid="organiser-controls"
        class="flex flex-col gap-3 rounded-xl border border-border p-4"
      >
        <h2 class="text-xs font-medium uppercase tracking-widest text-muted-foreground">
          Organiser
        </h2>
        <RouterLink
          :to="{ name: 'organise', params: { eventSlug: props.eventSlug } }"
          data-testid="organise-link"
          class="inline-flex items-center justify-center gap-x-2 rounded-lg border border-transparent bg-primary px-4 py-3 text-sm font-medium text-primary-foreground hover:bg-primary-hover focus:bg-primary-focus focus:outline-hidden"
        >
          Run the event
        </RouterLink>
      </section>

      <section
        v-if="venue.length > 0"
        class="rounded-xl border border-card-line bg-card p-5 shadow-2xs"
      >
        <h2 class="text-xs font-medium uppercase tracking-widest text-muted-foreground">
          Venue
        </h2>
        <p
          v-for="line in venue"
          :key="line"
          data-testid="venue-line"
          class="mt-1 text-sm text-foreground"
        >
          {{ line }}
        </p>
      </section>

      <section
        v-if="event.documents.length > 0"
        class="rounded-xl border border-card-line bg-card p-5 shadow-2xs"
      >
        <h2 class="text-xs font-medium uppercase tracking-widest text-muted-foreground">
          Documents
        </h2>
        <a
          v-for="document in event.documents"
          :key="document.id"
          :href="document.url"
          target="_blank"
          rel="noopener noreferrer"
          :data-testid="`document-${document.id}`"
          class="mt-2 block text-sm font-medium text-primary decoration-2 hover:underline focus:underline focus:outline-hidden"
        >
          {{ document.name }}
        </a>
      </section>
    </template>
  </main>
</template>
