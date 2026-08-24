<script setup lang="ts">
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, reactive, ref } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchEvent } from '@/api/events';
import { fetchFlags, resolveFlag, type ResultFlag } from '@/api/flags';
import { keys } from '@/api/keys';
import { correctGameResult, type Scores } from '@/api/results';
import MissingNotice from '@/components/MissingNotice.vue';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();
const queryClient = useQueryClient();

const { data: event, isPending: eventPending } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

/**
 * A reader without the permission is told the page is not there — the same
 * answer as an Event that does not exist, and for the same reason.
 */
const mayOrganise = computed(() => event.value?.viewer?.permissions.organise === true);
const forbidden = computed(() => event.value !== undefined && !mayOrganise.value);

const { data: flags } = useQuery({
  queryKey: computed(() => keys.flags(props.eventSlug)),
  queryFn: () => fetchFlags(client, props.eventSlug),
  enabled: mayOrganise,
  retry: false,
});

const queue = computed(() => flags.value ?? []);

/**
 * The Victory Points as an Organiser is editing them, keyed by Attendee.
 *
 * Seeded from the stored scores rather than left blank: a correction is
 * usually one number of the pair, and retyping the other invites a new
 * mistake into a screen that exists to fix one.
 */
const corrections = reactive<Record<number, string>>({});

function editable(flag: ResultFlag): { id: number; name: string; value: string }[] {
  return (flag.game?.attendees ?? []).map((attendee) => {
    corrections[attendee.id] ??= String(Number(attendee.scores['victory-points'] ?? 0));

    return { id: attendee.id, name: attendee.name, value: corrections[attendee.id] ?? '' };
  });
}

const working = ref<number | null>(null);
const problem = ref<string | null>(null);

/**
 * Put the score right and close the flag together.
 *
 * The API keeps the two apart on purpose — a flag can be cleared without a
 * change — but an Organiser who has just decided the score should not have to
 * remember a second step, and a corrected result left flagged reads to
 * everyone else as a dispute still open.
 */
async function correct(flag: ResultFlag): Promise<void> {
  const attendees = flag.game?.attendees ?? [];

  if (attendees.length === 0) {
    return;
  }

  working.value = flag.id;
  problem.value = null;

  const scores: Scores = Object.fromEntries(attendees.map((attendee) => [
    attendee.id,
    { 'victory-points': Number(corrections[attendee.id] ?? 0) },
  ]));

  try {
    await correctGameResult(client, props.eventSlug, flag.game_id, scores);
    await close(flag);
  } catch (caught) {
    problem.value = caught instanceof ApiError ? caught.message : 'That could not be saved.';
  } finally {
    working.value = null;
  }
}

/** Close a flag whose result turned out to be right after all. */
async function dismiss(flag: ResultFlag): Promise<void> {
  working.value = flag.id;
  problem.value = null;

  try {
    await close(flag);
  } catch (caught) {
    problem.value = caught instanceof ApiError ? caught.message : 'That could not be saved.';
  } finally {
    working.value = null;
  }
}

/**
 * Standings are computed from the Games, so they go stale with the score.
 */
async function close(flag: ResultFlag): Promise<void> {
  await resolveFlag(client, props.eventSlug, flag.game_id);

  await queryClient.invalidateQueries({ queryKey: keys.flags(props.eventSlug) });
  await queryClient.invalidateQueries({ queryKey: keys.standings(props.eventSlug) });
  await queryClient.invalidateQueries({ queryKey: keys.rounds(props.eventSlug) });
}

function title(flag: ResultFlag): string {
  const round = flag.game?.round;

  if (round === undefined) {
    return 'Game';
  }

  return round.name ?? `Round ${round.number}`;
}
</script>

<template>
  <main class="mx-auto flex min-h-screen w-full max-w-md flex-col gap-5 p-5">
    <p
      v-if="eventPending"
      class="text-ink-muted"
    >
      Loading…
    </p>

    <MissingNotice
      v-else-if="forbidden || event === undefined"
      thing="page"
    />

    <template v-else>
      <RouterLink
        :to="{ name: 'organise', params: { eventSlug: props.eventSlug } }"
        data-testid="back-to-organise"
        class="text-sm text-ink-muted underline underline-offset-4"
      >
        Back to running the event
      </RouterLink>

      <header class="flex flex-col gap-1">
        <p class="text-sm uppercase tracking-widest text-ink-faint">
          {{ event.name }}
        </p>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">
          Disputed results
        </h1>
      </header>

      <p
        v-if="queue.length === 0"
        data-testid="no-flags"
        class="rounded-2xl bg-surface-raised p-5 text-ink-muted"
      >
        Nothing is disputed. Flagged results turn up here.
      </p>

      <section
        v-for="queued in queue"
        :key="queued.id"
        :data-testid="`flag-${queued.id}`"
        class="flex flex-col gap-3 rounded-2xl bg-surface-raised p-5"
      >
        <h2 class="text-sm uppercase tracking-widest text-ink-faint">
          {{ title(queued) }} · Table {{ queued.game?.table_number }}
        </h2>

        <p
          v-if="queued.reason"
          class="text-ink"
        >
          “{{ queued.reason }}”
        </p>
        <p class="text-sm text-ink-muted">
          Flagged by {{ queued.flagged_by.name }}
        </p>

        <label
          v-for="side in editable(queued)"
          :key="side.id"
          class="flex items-center justify-between gap-4"
        >
          <span class="min-w-0 flex-1 truncate text-ink">{{ side.name }}</span>
          <input
            v-model="corrections[side.id]"
            type="number"
            inputmode="numeric"
            :data-testid="`flag-score-${side.id}`"
            class="w-24 rounded-lg border border-border bg-surface-sunken px-3 py-2.5 text-right text-lg tabular-nums text-ink outline-none focus:border-accent"
          >
        </label>

        <button
          type="button"
          :data-testid="`correct-flag-${queued.id}`"
          :disabled="working === queued.id"
          class="rounded-xl bg-accent px-4 py-3 font-semibold text-accent-ink disabled:opacity-60"
          @click="correct(queued)"
        >
          {{ working === queued.id ? 'Saving…' : 'Correct and resolve' }}
        </button>

        <!-- A flag can be right about there being a dispute and wrong about
             the score, so clearing one is not the same as changing it. -->
        <button
          type="button"
          :data-testid="`dismiss-flag-${queued.id}`"
          :disabled="working === queued.id"
          class="rounded-xl border border-border px-4 py-3 text-ink disabled:opacity-60"
          @click="dismiss(queued)"
        >
          The result was right
        </button>
      </section>

      <p
        v-if="problem"
        data-testid="flags-problem"
        class="text-danger"
      >
        {{ problem }}
      </p>
    </template>
  </main>
</template>
