<script setup lang="ts">
import { ChevronRightIcon } from '@heroicons/vue/24/outline';
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
  <main class="mx-auto flex w-full max-w-md flex-col gap-5 p-5">
    <!-- The nav names this screen, so the heading is for a screen reader
         landing here from a deep link and costs no space. -->
    <h1 class="sr-only">
      Rounds
    </h1>

    <p
      v-if="isPending"
      class="text-muted-foreground-1"
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
      role="alert"
      class="text-destructive"
    >
      {{ (error as ApiError).message }}
    </p>

    <p
      v-else-if="empty"
      data-testid="rounds-empty"
      class="text-muted-foreground-1"
    >
      No rounds yet. Pairings appear here the moment the first round is published.
    </p>

    <ul
      v-else
      class="divide-y divide-card-divider overflow-hidden rounded-xl border border-card-line bg-card shadow-2xs"
    >
      <li
        v-for="round in rounds"
        :key="round.id"
      >
        <RouterLink
          :to="{ name: 'round', params: { eventSlug: props.eventSlug, roundId: round.id } }"
          :data-testid="`round-${round.id}`"
          class="flex items-center justify-between gap-3 px-4 py-3.5 hover:bg-muted-hover focus:bg-muted-hover focus:outline-hidden"
          :class="round.id === currentRound?.id ? 'bg-primary/10' : ''"
        >
          <span class="text-lg font-semibold text-foreground">{{ title(round) }}</span>

          <span
            v-if="round.id === currentRound?.id"
            data-testid="now-playing"
            class="inline-flex items-center rounded-full bg-primary px-2.5 py-1 text-xs font-medium uppercase tracking-wide text-primary-foreground"
          >
            Now
          </span>
          <!-- Only an Organiser is ever sent a Draft, so this label is not a
               Player's screen hiding something: they never received it. -->
          <span
            v-else-if="round.status === 'draft'"
            data-testid="draft-badge"
            class="inline-flex items-center rounded-full border border-border px-2.5 py-1 text-xs font-medium uppercase tracking-wide text-muted-foreground-1"
          >
            Draft
          </span>
          <ChevronRightIcon
            v-else
            class="size-4 shrink-0 text-muted-foreground"
          />
        </RouterLink>
      </li>
    </ul>
  </main>
</template>
