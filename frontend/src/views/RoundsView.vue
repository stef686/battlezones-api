<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query';
import { computed } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchEvent } from '@/api/events';
import { keys } from '@/api/keys';
import { fetchRounds } from '@/api/rounds';
import MissingNotice from '@/components/MissingNotice.vue';
import { useEventPulse } from '@/composables/useEventPulse';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();

const { data: event } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

const inProgress = computed(() => event.value?.status === 'active');

const { currentRound } = useEventPulse(() => props.eventSlug, inProgress);

const { data: rounds, isPending, error } = useQuery({
  queryKey: computed(() => keys.rounds(props.eventSlug)),
  queryFn: () => fetchRounds(client, props.eventSlug),
  retry: false,
});

const missing = computed(() => error.value instanceof ApiError && error.value.kind === 'not_found');
const empty = computed(() => rounds.value !== undefined && rounds.value.length === 0);

function title(round: { number: number; name: string | null }): string {
  return round.name ?? `Round ${round.number}`;
}
</script>

<template>
  <main class="mx-auto flex min-h-screen w-full max-w-md flex-col gap-5 p-5">
    <RouterLink
      :to="{ name: 'event', params: { eventSlug: props.eventSlug } }"
      data-testid="back-to-event"
      class="text-sm text-ink-muted underline underline-offset-4"
    >
      Back to the event
    </RouterLink>

    <h1 class="text-2xl font-semibold tracking-tight text-ink">
      Rounds
    </h1>

    <p
      v-if="isPending"
      class="text-ink-muted"
    >
      Loading the rounds…
    </p>

    <MissingNotice
      v-else-if="missing"
      thing="event"
    />

    <p
      v-else-if="error"
      data-testid="rounds-error"
      class="text-danger"
    >
      {{ (error as ApiError).message }}
    </p>

    <p
      v-else-if="empty"
      data-testid="rounds-empty"
      class="text-ink-muted"
    >
      No rounds yet. Pairings appear here the moment the first round is published.
    </p>

    <ul
      v-else
      class="flex flex-col gap-2"
    >
      <li
        v-for="round in rounds"
        :key="round.id"
      >
        <RouterLink
          :to="{ name: 'round', params: { eventSlug: props.eventSlug, roundId: round.id } }"
          :data-testid="`round-${round.id}`"
          class="flex items-center justify-between gap-3 rounded-2xl bg-surface-raised px-4 py-3.5"
          :class="round.id === currentRound?.id ? 'ring-1 ring-accent' : ''"
        >
          <span class="text-lg font-semibold text-ink">{{ title(round) }}</span>

          <span
            v-if="round.id === currentRound?.id"
            data-testid="round-now"
            class="rounded-md bg-accent px-2 py-0.5 text-xs font-semibold uppercase tracking-wide text-accent-ink"
          >
            Now
          </span>
          <!-- Only an Organiser is ever sent a Draft, so this label is not a
               Player's screen hiding something: they never received it. -->
          <span
            v-else-if="round.status === 'draft'"
            data-testid="round-draft"
            class="rounded-md border border-border px-2 py-0.5 text-xs font-semibold uppercase tracking-wide text-ink-faint"
          >
            Draft
          </span>
        </RouterLink>
      </li>
    </ul>
  </main>
</template>
