<script setup lang="ts">
import { ChevronLeftIcon } from '@heroicons/vue/24/outline';
import { useQuery } from '@tanstack/vue-query';
import { computed } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchEvent } from '@/api/events';
import { keys } from '@/api/keys';
import { fetchRound, type Pairing } from '@/api/rounds';
import MissingNotice from '@/components/MissingNotice.vue';
import { useEventPulse } from '@/composables/useEventPulse';

const props = defineProps<{ eventSlug: string; roundId: string }>();

const client = useApiClient();
const roundId = computed(() => Number(props.roundId));

const { data: event } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

useEventPulse(() => props.eventSlug, computed(() => event.value?.status === 'active'));

const { data: round, isPending, error } = useQuery({
  queryKey: computed(() => keys.round(props.eventSlug, roundId.value)),
  queryFn: () => fetchRound(client, props.eventSlug, roundId.value),
  retry: false,
});

// A Draft Round and a Round that does not exist both answer 404 to a Player.
const missing = computed(() => error.value instanceof ApiError && error.value.kind === 'not_found');

const title = computed(() => round.value === undefined
  ? 'Round'
  : round.value.name ?? `Round ${round.value.number}`);

/** Byes first would bury the tables; they are listed after them. */
const pairings = computed(() => [...(round.value?.games ?? [])].sort(byTable));

function byTable(left: Pairing, right: Pairing): number {
  if (left.is_bye !== right.is_bye) {
    return left.is_bye ? 1 : -1;
  }

  return (left.table_number ?? 0) - (right.table_number ?? 0);
}

function names(pairing: Pairing): string[] {
  return pairing.attendees.map((attendee) => attendee.name);
}
</script>

<template>
  <main class="mx-auto flex w-full max-w-md flex-col gap-5 p-5">
    <RouterLink
      :to="{ name: 'rounds', params: { eventSlug: props.eventSlug } }"
      data-testid="back-to-rounds"
      class="inline-flex items-center gap-x-1 self-start text-sm font-medium text-muted-foreground-1 hover:text-foreground focus:text-foreground focus:outline-hidden"
    >
      <ChevronLeftIcon
        class="size-4 shrink-0"
      />
      Back to the rounds
    </RouterLink>

    <p
      v-if="isPending"
      class="text-muted-foreground-1"
    >
      Loading the pairings…
    </p>

    <MissingNotice
      v-else-if="missing"
      thing="round"
    />

    <p
      v-else-if="error"
      data-testid="round-error"
      role="alert"
      class="text-destructive"
    >
      {{ (error as ApiError).message }}
    </p>

    <template v-else-if="round">
      <header class="flex items-baseline justify-between gap-3">
        <h1
          data-testid="round-name"
          class="text-2xl font-bold tracking-tight text-foreground"
        >
          {{ title }}
        </h1>
        <span
          v-if="round.status === 'draft'"
          data-testid="draft-badge"
          class="inline-flex shrink-0 items-center rounded-full border border-border px-2.5 py-1 text-xs font-medium uppercase tracking-wide text-muted-foreground-1"
        >
          Draft
        </span>
      </header>

      <p
        v-if="pairings.length === 0"
        data-testid="pairings-empty"
        class="text-muted-foreground-1"
      >
        No pairings in this round.
      </p>

      <ul
        v-else
        class="divide-y divide-card-divider overflow-hidden rounded-xl border border-card-line bg-card shadow-2xs"
      >
        <li
          v-for="pairing in pairings"
          :key="pairing.id"
          :data-testid="`pairing-${pairing.id}`"
          class="flex items-center gap-4 px-4 py-3.5"
        >
          <!-- The table number is what someone crossing the hall is looking
               for, so it leads the row and is read at a distance. -->
          <span
            data-testid="pairing-table"
            class="w-10 shrink-0 text-center text-2xl font-bold tabular-nums text-primary"
          >
            {{ pairing.is_bye ? '—' : pairing.table_number }}
          </span>

          <span class="flex min-w-0 flex-col">
            <span
              v-for="name in names(pairing)"
              :key="name"
              class="truncate text-sm text-foreground"
            >{{ name }}</span>

            <span
              v-if="pairing.is_bye"
              data-testid="pairing-bye"
              class="text-sm text-muted-foreground"
            >Bye</span>
            <span
              v-else-if="pairing.is_rematch"
              data-testid="pairing-rematch"
              class="text-sm text-muted-foreground"
            >Rematch</span>
          </span>
        </li>
      </ul>
    </template>
  </main>
</template>
