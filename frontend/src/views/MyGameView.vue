<script setup lang="ts">
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, ref } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { submitResult, type Game, type Scores } from '@/api/results';
import { useSessionStore } from '@/stores/session';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();
const session = useSessionStore();
const queryClient = useQueryClient();

const { data, isPending, error } = useQuery({
  queryKey: ['my-game', props.eventSlug],
  queryFn: () => client.get<{ data: Game | null }>(`/api/events/${props.eventSlug}/my-game`),
});

const game = computed(() => data.value?.data ?? null);
const mine = computed(() => game.value?.attendees[0] ?? null);
const theirs = computed(() => game.value?.attendees[1] ?? null);
const submitted = computed(() => game.value?.result.submitted_at !== null);

const myScore = ref<number | null>(null);
const theirScore = ref<number | null>(null);
const submitting = ref(false);
const notice = ref<string | null>(null);
const problem = ref<string | null>(null);

async function submit(): Promise<void> {
  if (game.value === null || mine.value === null || theirs.value === null || session.viewer === null) {
    return;
  }

  submitting.value = true;
  notice.value = null;
  problem.value = null;

  const scores: Scores = {
    [mine.value.id]: { 'victory-points': Number(myScore.value ?? 0) },
    [theirs.value.id]: { 'victory-points': Number(theirScore.value ?? 0) },
  };

  try {
    const outcome = await submitResult(client, props.eventSlug, game.value.id, scores, session.viewer.id);

    notice.value = outcome.status === 'conflict'
      ? outcome.message
      : 'Result recorded.';

    if (outcome.status === 'conflict') {
      problem.value = outcome.message;
      notice.value = null;
    }

    await queryClient.invalidateQueries({ queryKey: ['my-game', props.eventSlug] });
    await queryClient.invalidateQueries({ queryKey: ['standings', props.eventSlug] });
  } catch (caught) {
    problem.value = caught instanceof ApiError ? caught.message : 'That could not be sent.';
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <main class="mx-auto flex min-h-screen w-full max-w-md flex-col gap-6 p-5">
    <p
      v-if="isPending"
      class="text-ink-muted"
    >
      Loading your game…
    </p>

    <p
      v-else-if="error"
      data-testid="my-game-error"
      class="text-danger"
    >
      {{ (error as ApiError).message }}
    </p>

    <p
      v-else-if="game === null"
      data-testid="no-game"
      class="text-ink-muted"
    >
      No game yet. The next round has not been published.
    </p>

    <template v-else>
      <!-- The table number is what a Player is looking for across a hall,
           so it is the screen rather than a line on it. -->
      <section class="rounded-2xl bg-surface-raised px-6 py-8 text-center">
        <p class="text-sm uppercase tracking-widest text-ink-faint">
          {{ game.round.name ?? `Round ${game.round.number}` }} · Table
        </p>
        <p
          data-testid="table-number"
          class="mt-2 text-hall font-bold tabular-nums text-accent"
        >
          {{ game.table_number }}
        </p>
      </section>

      <section class="flex flex-col gap-1">
        <p class="text-sm text-ink-faint">
          You are playing
        </p>
        <p
          data-testid="opponent"
          class="text-xl font-semibold text-ink"
        >
          {{ theirs?.name }}
        </p>
      </section>

      <form
        v-if="!submitted"
        class="flex flex-col gap-4 rounded-2xl bg-surface-raised p-5"
        novalidate
        @submit.prevent="submit"
      >
        <h2 class="text-sm uppercase tracking-widest text-ink-faint">
          Victory points
        </h2>

        <label class="flex items-center justify-between gap-4">
          <span class="text-ink">{{ mine?.name }}</span>
          <input
            v-model.number="myScore"
            type="number"
            inputmode="numeric"
            data-testid="my-score"
            class="w-24 rounded-lg border border-border bg-surface-sunken px-3 py-2 text-right text-lg tabular-nums text-ink outline-none focus:border-accent"
          >
        </label>

        <label class="flex items-center justify-between gap-4">
          <span class="text-ink">{{ theirs?.name }}</span>
          <input
            v-model.number="theirScore"
            type="number"
            inputmode="numeric"
            data-testid="their-score"
            class="w-24 rounded-lg border border-border bg-surface-sunken px-3 py-2 text-right text-lg tabular-nums text-ink outline-none focus:border-accent"
          >
        </label>

        <button
          type="submit"
          data-testid="submit-result"
          :disabled="submitting"
          class="rounded-lg bg-accent px-4 py-3 font-semibold text-accent-ink disabled:opacity-60"
        >
          {{ submitting ? 'Sending…' : 'Submit result' }}
        </button>
      </form>

      <p
        v-else
        data-testid="result-submitted"
        class="rounded-2xl bg-surface-raised p-5 text-success"
      >
        Result recorded. Flag it with an organiser if it needs correcting.
      </p>

      <p
        v-if="notice"
        data-testid="notice"
        class="text-success"
      >
        {{ notice }}
      </p>
      <p
        v-if="problem"
        data-testid="problem"
        class="text-danger"
      >
        {{ problem }}
      </p>

      <RouterLink
        :to="{ name: 'standings', params: { eventSlug: props.eventSlug } }"
        data-testid="standings-link"
        class="mt-2 text-center text-ink-muted underline underline-offset-4"
      >
        See the standings
      </RouterLink>
    </template>
  </main>
</template>
