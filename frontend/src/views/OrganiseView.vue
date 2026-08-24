<script setup lang="ts">
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, ref } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchEvent } from '@/api/events';
import { fetchFlags } from '@/api/flags';
import { keys } from '@/api/keys';
import {
  fetchRound,
  fetchRounds,
  generateRound,
  publishRound,
  swapPairings,
  unpublishRound,
  type Pairing,
} from '@/api/rounds';
import { correctGameResult } from '@/api/results';
import { fetchStandings, positionsByAttendee } from '@/api/standings';
import AllegianceBadge from '@/components/AllegianceBadge.vue';
import MissingNotice from '@/components/MissingNotice.vue';
import { useEventPulse } from '@/composables/useEventPulse';
import { isOpposed, previewSwap } from '@/lib/pairing';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();
const queryClient = useQueryClient();

const { data: event, isPending: eventPending } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

/**
 * A reader without the permission is told the page is not there.
 *
 * The same answer as an Event that does not exist, for the same reason: the
 * existence of an Organiser screen for someone else's Event is not this
 * reader's business.
 */
const mayOrganise = computed(() => event.value?.viewer?.permissions.organise === true);
const forbidden = computed(() => event.value !== undefined && !mayOrganise.value);

useEventPulse(() => props.eventSlug, computed(() => event.value?.status === 'active'));

const { data: rounds } = useQuery({
  queryKey: computed(() => keys.rounds(props.eventSlug)),
  queryFn: () => fetchRounds(client, props.eventSlug),
  enabled: mayOrganise,
  retry: false,
});

const { data: standings } = useQuery({
  queryKey: computed(() => keys.standings(props.eventSlug)),
  queryFn: () => fetchStandings(client, props.eventSlug),
  enabled: mayOrganise,
  retry: false,
});

const positions = computed(() => positionsByAttendee(standings.value ?? []));

/**
 * Open disputes are counted here so the queue is somewhere an Organiser is
 * sent, rather than somewhere they have to remember to go and look.
 */
const { data: flags } = useQuery({
  queryKey: computed(() => keys.flags(props.eventSlug)),
  queryFn: () => fetchFlags(client, props.eventSlug),
  enabled: mayOrganise,
  retry: false,
});

const disputed = computed(() => (flags.value ?? []).length);

const draft = computed(() => (rounds.value ?? []).find((round) => round.status === 'draft') ?? null);
const live = computed(() => [...(rounds.value ?? [])]
  .filter((round) => round.status === 'live')
  .sort((left, right) => right.number - left.number)[0] ?? null);

/**
 * Both Rounds are read, not whichever one is more interesting.
 *
 * An Organiser reviewing the next Round still needs to see which tables are
 * holding it up — those are the two halves of the same decision, and showing
 * one at a time would hide the reason the other cannot be published yet.
 */
const { data: liveDetail } = useQuery({
  queryKey: computed(() => keys.round(props.eventSlug, live.value?.id ?? 0)),
  queryFn: () => fetchRound(client, props.eventSlug, live.value!.id),
  enabled: computed(() => mayOrganise.value && live.value !== null),
  retry: false,
});

const { data: draftDetail } = useQuery({
  queryKey: computed(() => keys.round(props.eventSlug, draft.value?.id ?? 0)),
  queryFn: () => fetchRound(client, props.eventSlug, draft.value!.id),
  enabled: computed(() => mayOrganise.value && draft.value !== null),
  retry: false,
});

const pairings = computed(() => [...(draftDetail.value?.games ?? [])].sort(byTable));

const outstanding = computed(() => (liveDetail.value?.games ?? [])
  .filter((game) => !game.is_bye && game.result.submitted_at === null)
  .sort(byTable));

const working = ref<'generate' | 'publish' | 'unpublish' | null>(null);
const problem = ref<string | null>(null);

function byTable(left: Pairing, right: Pairing): number {
  if (left.is_bye !== right.is_bye) {
    return left.is_bye ? 1 : -1;
  }

  return (left.table_number ?? 0) - (right.table_number ?? 0);
}

function title(round: { number: number; name: string | null }): string {
  return round.name ?? `Round ${round.number}`;
}

const unopposed = computed(() => event.value?.requires_allegiance !== true
  ? []
  : pairings.value.filter((pairing) => !isOpposed(pairing)));

const opposes = computed(() => event.value?.requires_allegiance === true);

/** The Game waiting for something to swap with. */
const chosen = ref<number | null>(null);
const swapping = ref(false);

const chosenPairing = computed(() => pairings.value.find((pairing) => pairing.id === chosen.value) ?? null);

const preview = computed(() => {
  const first = chosenPairing.value;
  const second = pairings.value.find((pairing) => pairing.id === partner.value) ?? null;

  if (first === null || second === null) {
    return null;
  }

  return previewSwap(first, second, opposes.value);
});

const partner = ref<number | null>(null);

function choose(pairingId: number): void {
  problem.value = null;

  if (chosen.value === null) {
    chosen.value = pairingId;

    return;
  }

  if (chosen.value === pairingId) {
    chosen.value = null;
    partner.value = null;

    return;
  }

  partner.value = pairingId;
}

function cancelSwap(): void {
  chosen.value = null;
  partner.value = null;
}

async function confirmSwap(): Promise<void> {
  if (draft.value === null || chosen.value === null || partner.value === null) {
    return;
  }

  swapping.value = true;
  problem.value = null;

  try {
    await swapPairings(client, props.eventSlug, draft.value.id, [chosen.value, partner.value]);
    await queryClient.invalidateQueries({ queryKey: keys.rounds(props.eventSlug) });
    cancelSwap();
  } catch (caught) {
    // The API knows things one Round cannot show — that a Bye has to stay
    // with the larger Allegiance, for one — so its refusal is the answer.
    problem.value = caught instanceof ApiError ? caught.message : 'Those games could not be swapped.';
  } finally {
    swapping.value = false;
  }
}

/** Victory points for a Bye, which has no opponent to agree them with. */
const byeScores = ref<Record<number, string>>({});
const scoringBye = ref<number | null>(null);

/**
 * The Byes whose points have been written.
 *
 * Entering a Bye's points changes nothing else on the screen — the win was
 * counted when the Round was paired — so without saying so the save leaves no
 * trace, and an Organiser walks away not knowing whether it took.
 */
const savedByes = ref<number[]>([]);

async function scoreBye(pairing: Pairing): Promise<void> {
  const attendee = pairing.attendees[0];

  if (attendee === undefined) {
    return;
  }

  scoringBye.value = pairing.id;
  problem.value = null;
  savedByes.value = savedByes.value.filter((id) => id !== pairing.id);

  try {
    await correctGameResult(client, props.eventSlug, pairing.id, {
      [attendee.id]: { 'victory-points': Number(byeScores.value[pairing.id] ?? 0) },
    });

    await queryClient.invalidateQueries({ queryKey: keys.rounds(props.eventSlug) });
    await queryClient.invalidateQueries({ queryKey: keys.standings(props.eventSlug) });
    savedByes.value = [...savedByes.value, pairing.id];
  } catch (caught) {
    problem.value = caught instanceof ApiError ? caught.message : 'Those points could not be saved.';
  } finally {
    scoringBye.value = null;
  }
}

/** Byes in the Round being played, which are what an Organiser has to score. */
const byes = computed(() => (liveDetail.value?.games ?? []).filter((game) => game.is_bye));

async function run(what: 'generate' | 'publish' | 'unpublish'): Promise<void> {
  working.value = what;
  problem.value = null;

  try {
    if (what === 'generate') {
      await generateRound(client, props.eventSlug);
    } else if (what === 'publish' && draft.value !== null) {
      await publishRound(client, props.eventSlug, draft.value.id);
    } else if (what === 'unpublish' && live.value !== null) {
      await unpublishRound(client, props.eventSlug, live.value.id);
    }

    // Everything about the Round moved, and so did what Players can see.
    await queryClient.invalidateQueries({ queryKey: keys.rounds(props.eventSlug) });
    await queryClient.invalidateQueries({ queryKey: keys.myGame(props.eventSlug) });
  } catch (caught) {
    // The API refuses these with a message naming what to put right — an
    // unpublished Round, a table that has not reported — so the message is
    // the whole answer, whether or not it arrived as a validation error.
    problem.value = caught instanceof ApiError ? caught.message : 'That could not be done.';
  } finally {
    working.value = null;
  }
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
        :to="{ name: 'event', params: { eventSlug: props.eventSlug } }"
        data-testid="back-to-event"
        class="text-sm text-ink-muted underline underline-offset-4"
      >
        Back to the event
      </RouterLink>

      <header class="flex flex-col gap-1">
        <p class="text-sm uppercase tracking-widest text-ink-faint">
          {{ event.name }}
        </p>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">
          Run the event
        </h1>
      </header>

      <RouterLink
        :to="{ name: 'flags', params: { eventSlug: props.eventSlug } }"
        data-testid="flags-link"
        class="flex items-center justify-between gap-3 rounded-2xl bg-surface-raised p-5 text-ink"
      >
        <span>Disputed results</span>
        <span
          class="text-sm tabular-nums"
          :class="disputed > 0 ? 'text-danger' : 'text-ink-muted'"
        >{{ disputed > 0 ? `${disputed} waiting` : 'None' }}</span>
      </RouterLink>

      <!-- What is holding up the next Round, first, because that is the
           question an Organiser is standing there asking. -->
      <section
        v-if="live"
        data-testid="outstanding"
        class="flex flex-col gap-2 rounded-2xl bg-surface-raised p-5"
      >
        <h2 class="text-sm uppercase tracking-widest text-ink-faint">
          {{ title(live) }} · still playing
        </h2>

        <p
          v-if="outstanding.length === 0"
          data-testid="all-reported"
          class="text-success"
        >
          Every table has reported.
        </p>

        <template v-else>
          <p
            data-testid="outstanding-count"
            class="text-lg text-ink"
          >
            {{ outstanding.length }} {{ outstanding.length === 1 ? 'table' : 'tables' }} to go
          </p>
          <ul class="flex flex-wrap gap-2">
            <li
              v-for="game in outstanding"
              :key="game.id"
              :data-testid="`outstanding-${game.id}`"
              class="rounded-lg border border-border px-3 py-1.5 text-lg font-semibold tabular-nums text-ink"
            >
              {{ game.table_number }}
            </li>
          </ul>
        </template>
      </section>

      <!-- A Bye has nobody to agree a result with, so its Victory Points are
           entered here. The win itself was awarded when the Round was paired. -->
      <section
        v-for="bye in byes"
        :key="bye.id"
        :data-testid="`bye-${bye.id}`"
        class="flex flex-col gap-3 rounded-2xl bg-surface-raised p-5"
      >
        <h2 class="text-sm uppercase tracking-widest text-ink-faint">
          Bye · {{ bye.attendees[0]?.name }}
        </h2>
        <p class="text-sm text-ink-muted">
          Counted as a win already. Enter the victory points they are credited with.
        </p>

        <div class="flex items-center gap-3">
          <input
            v-model="byeScores[bye.id]"
            type="number"
            inputmode="numeric"
            :data-testid="`bye-score-${bye.id}`"
            class="w-24 rounded-lg border border-border bg-surface-sunken px-3 py-2.5 text-right text-lg tabular-nums text-ink outline-none focus:border-accent"
          >
          <button
            type="button"
            :data-testid="`save-bye-${bye.id}`"
            :disabled="scoringBye === bye.id"
            class="flex-1 rounded-xl bg-accent px-4 py-3 font-semibold text-accent-ink disabled:opacity-60"
            @click="scoreBye(bye)"
          >
            {{ scoringBye === bye.id ? 'Saving…' : 'Save points' }}
          </button>
        </div>

        <p
          v-if="savedByes.includes(bye.id)"
          :data-testid="`bye-saved-${bye.id}`"
          class="text-sm text-success"
        >
          Saved. They are counted in the standings.
        </p>
      </section>

      <section class="flex flex-col gap-3">
        <h2 class="text-sm uppercase tracking-widest text-ink-faint">
          {{ draft ? 'Ready to publish' : 'Next round' }}
        </h2>

        <p
          v-if="!draft"
          data-testid="no-draft"
          class="text-ink-muted"
        >
          Pair the next round when every table has reported. Nothing is shown to players until you publish it.
        </p>

        <p
          v-else-if="unopposed.length > 0"
          data-testid="unopposed-warning"
          class="rounded-xl border border-danger px-4 py-3 text-sm text-danger"
        >
          {{ unopposed.length }} {{ unopposed.length === 1 ? 'game is' : 'games are' }} not between opposed
          allegiances. Check before publishing.
        </p>

        <!-- The review: every pairing, both sides, and where each team stands. -->
        <ul
          v-if="draft && draftDetail"
          class="flex flex-col gap-2"
        >
          <li
            v-for="pairing in pairings"
            :key="pairing.id"
            :data-testid="`review-${pairing.id}`"
            class="flex items-start gap-3 rounded-2xl bg-surface-raised px-4 py-3"
          >
            <span
              data-testid="review-table"
              class="w-8 shrink-0 pt-0.5 text-center text-xl font-bold tabular-nums text-accent"
            >
              {{ pairing.is_bye ? '—' : pairing.table_number }}
            </span>

            <span class="flex min-w-0 flex-1 flex-col gap-1.5">
              <span
                v-for="attendee in pairing.attendees"
                :key="attendee.id"
                class="flex items-center justify-between gap-2"
              >
                <span class="flex min-w-0 items-baseline gap-2">
                  <span
                    v-if="positions.get(attendee.id)"
                    data-testid="review-position"
                    class="shrink-0 text-sm tabular-nums text-ink-faint"
                  >#{{ positions.get(attendee.id) }}</span>
                  <span class="truncate text-ink">{{ attendee.name }}</span>
                </span>

                <AllegianceBadge :allegiance="attendee.allegiance" />
              </span>

              <span
                v-if="pairing.is_bye"
                data-testid="review-bye"
                class="text-sm text-ink-faint"
              >Bye</span>
              <span
                v-else-if="pairing.is_rematch"
                data-testid="review-rematch"
                class="text-sm text-danger"
              >Rematch</span>
            </span>

            <button
              type="button"
              :data-testid="`swap-${pairing.id}`"
              class="shrink-0 self-center rounded-lg border px-3 py-2 text-sm"
              :class="chosen === pairing.id ? 'border-accent text-accent' : 'border-border text-ink-muted'"
              @click="choose(pairing.id)"
            >
              {{ chosen === pairing.id ? 'Chosen' : 'Swap' }}
            </button>
          </li>
        </ul>

        <p
          v-if="chosen !== null && partner === null"
          data-testid="swap-prompt"
          class="text-sm text-ink-muted"
        >
          Now choose the game to swap it with.
        </p>

        <!-- What the swap would produce, before it is committed. -->
        <section
          v-if="preview"
          data-testid="swap-preview"
          class="flex flex-col gap-3 rounded-2xl border border-accent p-4"
        >
          <h3 class="text-sm uppercase tracking-widest text-ink-faint">
            After the swap
          </h3>

          <p
            v-if="!preview.ok"
            data-testid="swap-impossible"
            class="text-sm text-danger"
          >
            {{ preview.reason }}
          </p>

          <template v-else>
            <div
              v-for="game in preview.games"
              :key="game.id"
              :data-testid="`preview-${game.id}`"
              class="flex items-start gap-3"
            >
              <span class="w-8 shrink-0 text-center text-lg font-bold tabular-nums text-accent">
                {{ game.is_bye ? '—' : game.table_number }}
              </span>
              <span class="flex min-w-0 flex-1 flex-col gap-1">
                <span
                  v-for="attendee in game.attendees"
                  :key="attendee.id"
                  class="flex items-center justify-between gap-2"
                >
                  <span class="truncate text-ink">{{ attendee.name }}</span>
                  <AllegianceBadge :allegiance="attendee.allegiance" />
                </span>
              </span>
            </div>

            <p
              v-if="opposes && !preview.opposed"
              data-testid="swap-unopposed"
              class="text-sm text-danger"
            >
              This swap leaves a game that is not between opposed allegiances.
            </p>
          </template>

          <div class="flex gap-3">
            <button
              type="button"
              data-testid="confirm-swap"
              :disabled="swapping || !preview.ok"
              class="flex-1 rounded-xl bg-accent px-4 py-3 font-semibold text-accent-ink disabled:opacity-60"
              @click="confirmSwap"
            >
              {{ swapping ? 'Swapping…' : 'Swap them' }}
            </button>
            <button
              type="button"
              data-testid="cancel-swap"
              class="rounded-xl border border-border px-4 py-3 text-ink"
              @click="cancelSwap"
            >
              Cancel
            </button>
          </div>
        </section>
      </section>

      <p
        v-if="problem"
        data-testid="organise-problem"
        class="rounded-xl border border-danger px-4 py-3 text-sm text-danger"
      >
        {{ problem }}
      </p>

      <!-- Full-width, thumb-height, one under another: this is operated with
           one hand while standing up in a hall. -->
      <div class="flex flex-col gap-3">
        <button
          v-if="draft"
          type="button"
          data-testid="publish-round"
          :disabled="working !== null"
          class="rounded-xl bg-accent px-4 py-4 text-lg font-semibold text-accent-ink disabled:opacity-60"
          @click="run('publish')"
        >
          {{ working === 'publish' ? 'Publishing…' : `Publish ${title(draft)}` }}
        </button>

        <button
          v-else
          type="button"
          data-testid="generate-round"
          :disabled="working !== null"
          class="rounded-xl bg-accent px-4 py-4 text-lg font-semibold text-accent-ink disabled:opacity-60"
          @click="run('generate')"
        >
          {{ working === 'generate' ? 'Pairing…' : 'Pair the next round' }}
        </button>

        <button
          v-if="live && !draft"
          type="button"
          data-testid="unpublish-round"
          :disabled="working !== null"
          class="rounded-xl border border-border px-4 py-4 text-lg text-ink disabled:opacity-60"
          @click="run('unpublish')"
        >
          {{ working === 'unpublish' ? 'Withdrawing…' : `Withdraw ${title(live)}` }}
        </button>
      </div>

      <RouterLink
        :to="{ name: 'rounds', params: { eventSlug: props.eventSlug } }"
        data-testid="rounds-link"
        class="text-center text-ink-muted underline underline-offset-4"
      >
        All rounds
      </RouterLink>
    </template>
  </main>
</template>
