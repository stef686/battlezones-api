<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query';
import { computed } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchEvent } from '@/api/events';
import { keys } from '@/api/keys';
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
</script>

<template>
  <main class="mx-auto flex min-h-screen w-full max-w-md flex-col gap-6 p-5">
    <p
      v-if="isPending"
      class="text-ink-muted"
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
      class="text-danger"
    >
      {{ (error as ApiError).message }}
    </p>

    <template v-else-if="event">
      <header class="flex flex-col gap-2">
        <p
          v-if="event.game_system"
          class="text-sm uppercase tracking-widest text-ink-faint"
        >
          {{ event.game_system.name }}
        </p>
        <h1
          data-testid="event-name"
          class="text-2xl font-semibold tracking-tight text-ink"
        >
          {{ event.name }}
        </h1>
        <p
          v-if="dates"
          data-testid="event-dates"
          class="text-ink-muted"
        >
          {{ dates }}
        </p>
        <p
          v-if="places"
          data-testid="event-places"
          class="text-sm text-ink-faint"
        >
          {{ places }}
        </p>
      </header>

      <p
        v-if="event.description"
        data-testid="event-description"
        class="whitespace-pre-line text-ink-muted"
      >
        {{ event.description }}
      </p>

      <nav class="flex flex-col gap-2">
        <RouterLink
          :to="{ name: 'schedule', params: { eventSlug: props.eventSlug } }"
          data-testid="schedule-link"
          class="rounded-xl bg-surface-raised px-4 py-3 text-ink"
        >
          Schedule
        </RouterLink>
        <RouterLink
          :to="{ name: 'rounds', params: { eventSlug: props.eventSlug } }"
          data-testid="rounds-link"
          class="rounded-xl bg-surface-raised px-4 py-3 text-ink"
        >
          Rounds and pairings
        </RouterLink>
        <RouterLink
          :to="{ name: 'attendees', params: { eventSlug: props.eventSlug } }"
          data-testid="attendees-link"
          class="rounded-xl bg-surface-raised px-4 py-3 text-ink"
        >
          Who is here
        </RouterLink>
        <RouterLink
          :to="{ name: 'standings', params: { eventSlug: props.eventSlug } }"
          data-testid="standings-link"
          class="rounded-xl bg-surface-raised px-4 py-3 text-ink"
        >
          Standings
        </RouterLink>
        <RouterLink
          v-if="viewer?.is_attendee"
          :to="{ name: 'my-team', params: { eventSlug: props.eventSlug } }"
          data-testid="my-team-link"
          class="rounded-xl bg-surface-raised px-4 py-3 text-ink"
        >
          Your team
        </RouterLink>
        <RouterLink
          v-else-if="viewer?.permissions.register"
          :to="{ name: 'register', params: { eventSlug: props.eventSlug } }"
          data-testid="register-link"
          class="rounded-xl bg-accent px-4 py-3 font-semibold text-accent-ink"
        >
          Enter this event
        </RouterLink>
      </nav>

      <!-- Organiser controls exist only where the viewer context grants them,
           never hidden by CSS: an unauthorised reader is not sent them. -->
      <section
        v-if="viewer?.permissions.organise"
        data-testid="organiser-controls"
        class="flex flex-col gap-2 rounded-2xl border border-border p-4"
      >
        <h2 class="text-sm uppercase tracking-widest text-ink-faint">
          Organiser
        </h2>
        <p class="text-sm text-ink-muted">
          You run this event.
        </p>
      </section>

      <section
        v-if="venue.length > 0"
        class="flex flex-col gap-1 rounded-2xl bg-surface-raised p-5"
      >
        <h2 class="text-sm uppercase tracking-widest text-ink-faint">
          Venue
        </h2>
        <p
          v-for="line in venue"
          :key="line"
          data-testid="venue-line"
          class="text-ink"
        >
          {{ line }}
        </p>
      </section>

      <section
        v-if="event.documents.length > 0"
        class="flex flex-col gap-2 rounded-2xl bg-surface-raised p-5"
      >
        <h2 class="text-sm uppercase tracking-widest text-ink-faint">
          Documents
        </h2>
        <a
          v-for="document in event.documents"
          :key="document.id"
          :href="document.url"
          target="_blank"
          rel="noopener noreferrer"
          :data-testid="`document-${document.id}`"
          class="text-ink underline underline-offset-4"
        >
          {{ document.name }}
        </a>
      </section>
    </template>
  </main>
</template>
