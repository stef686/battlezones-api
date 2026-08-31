<script setup lang="ts">
/**
 * One Round's pairings, and the way to every other Round.
 *
 * This is where the Rounds tab lands, so it carries the moving between Rounds
 * that a list used to: the Round's name centred, a chevron either side. A
 * Player checking what they played last round is one tap from it rather than
 * one tap back to a menu and one tap in again.
 *
 * The search filters what is already on screen rather than asking the API for
 * it. A Round is at most a few dozen Games, they are already in hand, and a
 * hall's wifi is the wrong place to spend a request per keystroke.
 */
import { ChevronLeft, ChevronRight, Repeat } from 'lucide-vue-next';
import { useQuery } from '@tanstack/vue-query';
import { computed, ref } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchEvent } from '@/api/events';
import { keys } from '@/api/keys';
import { byNumber, fetchRound, fetchRounds, listedColumns, roundTitle, tableLabel, type Pairing, type RoundSummary } from '@/api/rounds';
import GameScoreTable from '@/components/GameScoreTable.vue';
import MissingNotice from '@/components/MissingNotice.vue';
import TextField from '@/components/TextField.vue';
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

/**
 * The Round's neighbours. Read from the same cached list the Rounds tab
 * resolved through, so arriving by chevron costs nothing.
 */
const { data: rounds } = useQuery({
  queryKey: computed(() => keys.rounds(props.eventSlug)),
  queryFn: () => fetchRounds(client, props.eventSlug),
  retry: false,
});

// A Draft Round and a Round that does not exist both answer 404 to a Player.
const missing = computed(() => error.value instanceof ApiError && error.value.kind === 'not_found');

const title = computed(() => round.value === undefined ? 'Round' : roundTitle(round.value));

const ordered = computed(() => byNumber(rounds.value ?? []));
const position = computed(() => ordered.value.findIndex((sibling) => sibling.id === roundId.value));

const previous = computed<RoundSummary | null>(() => position.value < 0 ? null : ordered.value[position.value - 1] ?? null);
const next = computed<RoundSummary | null>(() => position.value < 0 ? null : ordered.value[position.value + 1] ?? null);

/**
 * The search is deliberately not cleared when the Round changes: a Player
 * following one team through the Event holds the filter and walks the
 * chevrons. The empty state names what was searched for, so a Round that team
 * did not play in explains itself rather than reading as a Round with no Games.
 */
const search = ref('');

/** Byes first would bury the tables; they are listed after them. */
const pairings = computed(() => [...(round.value?.games ?? [])].sort(byTable).filter(matchesSearch));

const nothingMatched = computed(() => (round.value?.games.length ?? 0) > 0 && pairings.value.length === 0);

function matchesSearch(pairing: Pairing): boolean {
  const term = search.value.trim().toLowerCase();

  return term === '' || names(pairing).some((name) => name.toLowerCase().includes(term));
}

function byTable(left: Pairing, right: Pairing): number {
  if (left.is_bye !== right.is_bye) {
    return left.is_bye ? 1 : -1;
  }

  return (left.table_number ?? 0) - (right.table_number ?? 0);
}

function names(pairing: Pairing): string[] {
  return pairing.attendees.map((attendee) => attendee.name);
}

/**
 * The score column the cards carry: the Event's primary one, and only it.
 *
 * Read from the Round rather than from the scores in hand: a Game nobody has
 * played yet carries no scores at all, and inferring the column from what was
 * entered would leave it blank instead of showing what it is waiting for.
 *
 * The rest are not dropped, only not listed — an Event can be scored on half
 * a dozen columns, and a Player scanning a hall wants the number that was
 * played for, not the whole scoresheet. The Game screen has room for the lot.
 */
const columns = computed(() => listedColumns(round.value?.score_types ?? []));

</script>

<template>
  <!-- Tighter above and between than the other screens: the Round is a list
       read at arm's length in a hall, and every line the chrome takes is a
       Game the reader has to scroll for. -->
  <main class="mx-auto flex w-full max-w-md flex-col gap-3 px-5 pt-4 pb-5">
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
      <!-- The name is centred between the chevrons rather than pushed left,
           so the two ways out of the Round are equally weighted and the thumb
           finds either without looking. -->
      <!-- Pulled in by the padding the chevrons carry, rather than by taking
           that padding off them: it is there to give a thumb something to hit
           on a moving train, and the space it costs the layout is bought back
           here instead. -->
      <header class="-my-1 flex items-center justify-between gap-2">
        <!-- Both chevrons are always drawn, greyed where there is no Round
             that way. An arrow that vanishes at the ends moves the name
             sideways and leaves a Player guessing whether they have reached
             the first Round or the control simply is not there; a dead arrow
             says "this is the end" without moving anything. The dead one is
             hidden from a screen reader rather than announced as a disabled
             link, because silence already says there is nowhere to go. -->
        <RouterLink
          v-if="previous"
          :to="{ name: 'round', params: { eventSlug: props.eventSlug, roundId: previous.id } }"
          data-testid="previous-round"
          :aria-label="`Go to ${roundTitle(previous)}`"
          class="rounded-lg p-2 text-primary hover:text-primary-hover focus:text-primary-hover focus:outline-hidden"
        >
          <ChevronLeft class="size-6 shrink-0" />
        </RouterLink>
        <span
          v-else
          data-testid="previous-round"
          aria-hidden="true"
          class="rounded-lg p-2 text-muted-foreground"
        >
          <ChevronLeft class="size-6 shrink-0" />
        </span>

        <span class="flex min-w-0 flex-col items-center gap-1">
          <h1
            data-testid="round-name"
            class="truncate text-base font-bold tracking-tight text-foreground"
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
        </span>

        <RouterLink
          v-if="next"
          :to="{ name: 'round', params: { eventSlug: props.eventSlug, roundId: next.id } }"
          data-testid="next-round"
          :aria-label="`Go to ${roundTitle(next)}`"
          class="rounded-lg p-2 text-primary hover:text-primary-hover focus:text-primary-hover focus:outline-hidden"
        >
          <ChevronRight class="size-6 shrink-0" />
        </RouterLink>
        <span
          v-else
          data-testid="next-round"
          aria-hidden="true"
          class="rounded-lg p-2 text-muted-foreground"
        >
          <ChevronRight class="size-6 shrink-0" />
        </span>
      </header>

      <!-- The placeholder carries what a label and a hint used to, because
           this is one field on a screen whose whole job is the list beneath
           it. The label is still there for a screen reader, just not on
           screen. -->
      <TextField
        v-if="round.games.length > 0"
        v-model="search"
        label="Search by team name"
        label-hidden
        type="search"
        placeholder="Search by team name…"
        testid="game-search"
      />

      <p
        v-if="round.games.length === 0"
        data-testid="pairings-empty"
        class="text-muted-foreground-1"
      >
        No pairings in this round.
      </p>

      <p
        v-else-if="nothingMatched"
        data-testid="pairings-unmatched"
        class="text-muted-foreground-1"
      >
        No team in this round matches “{{ search.trim() }}”.
      </p>

      <!-- One block per Game rather than one row: a Game is a table, two teams
           and a scoreline, and a single divided list flattened all three into
           a line that a Player had to parse rather than read. Nothing is
           drawn around them — the gap between the blocks separates them, and
           on a phone a run of boxed cards is chrome the scoreline has to be
           read through. They keep the screen's own gutters, so the names
           start on the same line the search field does.

           A rule between them rather than a gap alone: with the scores pinned
           to one edge, a Player following a column down the screen needs to
           see where one Game's two teams end and the next Game's begin. It
           divides rather than boxes — no line above the first or below the
           last, which would read as a frame again. -->
      <ul
        v-else
        data-testid="pairings"
        class="flex flex-col divide-y divide-card-divider"
      >
        <li
          v-for="pairing in pairings"
          :key="pairing.id"
          :data-testid="`pairing-${pairing.id}`"
          class="py-3"
        >
          <!-- The whole card is the target rather than a link tucked inside
               it: a Player looking for what their opponent brought is aiming
               at a card across a hall, not at a word on one. -->
          <RouterLink
            :to="{ name: 'game', params: { eventSlug: props.eventSlug, gameId: pairing.id } }"
            :data-testid="`open-game-${pairing.id}`"
            class="block rounded-lg focus:outline-hidden focus-visible:bg-background-1"
          >
            <!-- The table number leads, because it is what somebody crossing a
                 hall is looking for. What state the Game is in sits opposite it,
                 where a reader scanning down the cards finds it in one column. -->
            <header class="flex items-center justify-between gap-3 py-2">
              <span class="flex min-w-0 items-center gap-1.5">
                <!-- Where the Game is and whether it is done are both labels
                     rather than running text: they are the two facts a reader
                     crossing a hall picks a card out by, and set on their own
                     ground they are found without reading the row. -->
                <span
                  data-testid="pairing-table"
                  class="game-label"
                >
                  {{ tableLabel(pairing) }}
                </span>

                <!-- Beside the table, because it qualifies the pairing rather
                     than reporting on it: an Organiser scanning a Draft reads
                     "table 5, and these two have met" in one go. Rendered on the
                     key being present, which the API sends to nobody else — it
                     is not a Player's screen hiding something. -->
                <span
                  v-if="pairing.is_rematch"
                  data-testid="pairing-rematch"
                  class="inline-flex shrink-0 items-center text-muted-foreground"
                >
                  <Repeat class="size-3.5 shrink-0" />
                  <!-- The icon carries no name of its own, and "these two have
                       already met" is not a thing to leave to a glyph. -->
                  <span class="sr-only">Rematch</span>
                </span>
              </span>

              <span
                v-if="pairing.result.submitted_at"
                data-testid="pairing-finished"
                class="game-label shrink-0"
              >
                Finished
              </span>
            </header>

            <!-- The initials are dropped here and only here: the same three
                 columns repeat down every Game in the Round, so naming them
                 once per card is noise a Player reads past. They stay in the
                 markup for a screen reader, which has no such run to read. -->
            <GameScoreTable
              :attendees="pairing.attendees"
              :columns="columns"
              variant="listing"
            />
          </RouterLink>
        </li>
      </ul>
    </template>
  </main>
</template>
