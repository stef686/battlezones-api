<script setup lang="ts">
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, ref } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchEvent } from '@/api/events';
import { flagResult } from '@/api/flags';
import { keys } from '@/api/keys';
import { submitResult, type Game, type Scores } from '@/api/results';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import { useEventPulse } from '@/composables/useEventPulse';
import { useSessionStore } from '@/stores/session';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();
const session = useSessionStore();
const queryClient = useQueryClient();

const { data: event } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

// This is the screen a Player leaves open on the table, so it is the one that
// most has to notice the next Round being published without being asked.
useEventPulse(() => props.eventSlug, computed(() => event.value?.status === 'active'));

const { data, isPending, error } = useQuery({
  queryKey: computed(() => keys.myGame(props.eventSlug)),
  queryFn: () => client.get<{ data: Game | null }>(`/api/events/${props.eventSlug}/my-game`),
});

const game = computed(() => data.value?.data ?? null);
const mine = computed(() => game.value?.attendees[0] ?? null);
const theirs = computed(() => game.value?.attendees[1] ?? null);
const submitted = computed(() => game.value?.result.submitted_at !== null);

/**
 * A Bye has no opponent, so there is nothing for a Player to agree and
 * nothing to submit. The API refuses a result for one; offering the form
 * would be inviting them to fail.
 */
const isBye = computed(() => game.value?.is_bye === true);

const myScore = ref<number | null>(null);
const theirScore = ref<number | null>(null);
const submitting = ref(false);
const notice = ref<string | null>(null);
const problem = ref<string | null>(null);

const flagReason = ref('');
const flagging = ref(false);

async function flag(): Promise<void> {
  if (game.value === null) {
    return;
  }

  flagging.value = true;
  notice.value = null;
  problem.value = null;

  try {
    await flagResult(client, props.eventSlug, game.value.id, flagReason.value);
    await queryClient.invalidateQueries({ queryKey: keys.myGame(props.eventSlug) });
  } catch (caught) {
    problem.value = caught instanceof ApiError ? caught.message : 'That could not be sent.';
  } finally {
    flagging.value = false;
  }
}

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

    await queryClient.invalidateQueries({ queryKey: keys.myGame(props.eventSlug) });
    await queryClient.invalidateQueries({ queryKey: keys.standings(props.eventSlug) });
  } catch (caught) {
    problem.value = caught instanceof ApiError ? caught.message : 'That could not be sent.';
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <main class="mx-auto flex w-full max-w-md flex-col gap-6 p-5">
    <p
      v-if="isPending"
      class="text-muted-foreground-1"
    >
      Loading your game…
    </p>

    <p
      v-else-if="error"
      data-testid="my-game-error"
      role="alert"
      class="text-destructive"
    >
      {{ (error as ApiError).message }}
    </p>

    <p
      v-else-if="game === null"
      data-testid="no-game"
      class="text-muted-foreground-1"
    >
      No game yet. The next round has not been published.
    </p>

    <template v-else>
      <!-- The table number is what a Player is looking for across a hall,
           so it is the screen rather than a line on it. -->
      <section class="rounded-xl border border-card-line bg-card px-6 py-8 text-center shadow-2xs">
        <p class="text-xs font-medium uppercase tracking-widest text-muted-foreground">
          {{ game.round.name ?? `Round ${game.round.number}` }} · {{ isBye ? 'No table' : 'Table' }}
        </p>
        <p
          data-testid="table-number"
          class="mt-2 text-hall font-bold tabular-nums text-primary"
        >
          {{ isBye ? '—' : game.table_number }}
        </p>
      </section>

      <section
        v-if="isBye"
        data-testid="bye-notice"
        class="rounded-xl border border-card-line bg-card p-5 shadow-2xs"
      >
        <p class="text-base font-semibold text-foreground">
          You have the bye this round.
        </p>
        <p class="mt-1 text-sm text-muted-foreground-1">
          It counts as a win. An organiser enters the victory points, so there is nothing for you to submit.
        </p>
      </section>

      <section v-else>
        <p class="text-xs font-medium uppercase tracking-widest text-muted-foreground">
          You are playing
        </p>
        <!-- Their team page is where a revealed army list is read, which is
             the whole reason to look them up before the game. -->
        <RouterLink
          v-if="theirs"
          :to="{ name: 'attendee', params: { eventSlug: props.eventSlug, attendeeId: theirs.id } }"
          data-testid="opponent-link"
          class="mt-1 inline-block text-xl font-semibold text-primary decoration-2 hover:underline focus:underline focus:outline-hidden"
        >
          <span data-testid="opponent">{{ theirs.name }}</span>
        </RouterLink>
      </section>

      <form
        v-if="!submitted && !isBye"
        class="flex flex-col gap-4 rounded-xl border border-card-line bg-card p-5 shadow-2xs"
        novalidate
        @submit.prevent="submit"
      >
        <h2 class="text-xs font-medium uppercase tracking-widest text-muted-foreground">
          Victory points
        </h2>

        <label class="flex items-center justify-between gap-4">
          <span class="min-w-0 truncate text-sm text-foreground">{{ mine?.name }}</span>
          <input
            v-model.number="myScore"
            type="number"
            inputmode="numeric"
            data-testid="my-score"
            class="w-24 shrink-0 rounded-lg border border-border bg-background-2 px-3 py-2 text-right text-lg tabular-nums text-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-hidden"
          >
        </label>

        <label class="flex items-center justify-between gap-4">
          <span class="min-w-0 truncate text-sm text-foreground">{{ theirs?.name }}</span>
          <input
            v-model.number="theirScore"
            type="number"
            inputmode="numeric"
            data-testid="their-score"
            class="w-24 shrink-0 rounded-lg border border-border bg-background-2 px-3 py-2 text-right text-lg tabular-nums text-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-hidden"
          >
        </label>

        <AppButton
          type="submit"
          data-testid="submit-result"
          :disabled="submitting"
          block
        >
          {{ submitting ? 'Sending…' : 'Submit result' }}
        </AppButton>
      </form>

      <template v-else-if="!isBye">
        <AppAlert
          data-testid="result-submitted"
          tone="success"
        >
          Result recorded.
        </AppAlert>

        <AppAlert
          v-if="game.result.edited_by"
          data-testid="result-corrected"
        >
          Corrected by {{ game.result.edited_by.name }}.
        </AppAlert>

        <AppAlert
          v-if="game.result.is_flagged"
          data-testid="result-flagged"
        >
          An organiser is looking at this result. They will correct it if it is wrong.
        </AppAlert>

        <!-- A result cannot be resubmitted, so disagreeing means asking an
             Organiser to look at it. -->
        <section
          v-else
          data-testid="flag-form"
          class="flex flex-col gap-3 rounded-xl border border-card-line bg-card p-5 shadow-2xs"
        >
          <h2 class="text-xs font-medium uppercase tracking-widest text-muted-foreground">
            Something wrong with it?
          </h2>
          <textarea
            v-model="flagReason"
            data-testid="flag-reason"
            rows="3"
            placeholder="What should it have been?"
            class="block w-full rounded-lg border border-border bg-background-2 px-4 py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-hidden"
          />
          <AppButton
            data-testid="flag-result"
            variant="secondary"
            :disabled="flagging"
            block
            @click="flag"
          >
            {{ flagging ? 'Sending…' : 'Flag for an organiser' }}
          </AppButton>
        </section>
      </template>

      <p
        v-if="notice"
        data-testid="notice"
        role="status"
        class="text-sm text-success"
      >
        {{ notice }}
      </p>
      <p
        v-if="problem"
        data-testid="problem"
        role="alert"
        class="text-sm text-destructive"
      >
        {{ problem }}
      </p>

      <RouterLink
        :to="{ name: 'standings', params: { eventSlug: props.eventSlug } }"
        data-testid="standings-link"
        class="mt-2 text-center text-sm font-medium text-primary decoration-2 hover:underline focus:underline focus:outline-hidden"
      >
        See the standings
      </RouterLink>
    </template>
  </main>
</template>
