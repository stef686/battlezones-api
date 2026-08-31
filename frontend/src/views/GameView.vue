<script setup lang="ts">
/**
 * One Game: the scoreline, and the lists behind it.
 *
 * The Round screen shows a hall of Games at a glance; this is the one a
 * Player tapped, so it leads with the same score table they just tapped —
 * unchanged, so the tap reads as opening rather than as navigating somewhere
 * else — and then gives over the rest of the screen to what the Round has no
 * room for: what each side brought.
 *
 * The lists are tabbed by team rather than stacked because they are long, and
 * a team is the unit a Player prepares against: both of a doubles pair's
 * lists are read together, and scrolling past the opposing team's to reach
 * them is the whole cost of a doubles Event.
 */
import { ChevronLeft } from 'lucide-vue-next';
import { useQuery } from '@tanstack/vue-query';
import { computed, ref, watch } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchEvent } from '@/api/events';
import { keys } from '@/api/keys';
import { fetchGame, type GameAttendee, type GameMember } from '@/api/results';
import { roundTitle } from '@/api/rounds';
import GameScoreTable from '@/components/GameScoreTable.vue';
import MissingNotice from '@/components/MissingNotice.vue';
import { useEventPulse } from '@/composables/useEventPulse';

const props = defineProps<{ eventSlug: string; gameId: string }>();

const client = useApiClient();
const gameId = computed(() => Number(props.gameId));

const { data: event } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

// A result landing while this is open changes the table at the top of it.
useEventPulse(() => props.eventSlug, computed(() => event.value?.status === 'active'));

const { data: game, isPending, error } = useQuery({
  queryKey: computed(() => keys.game(props.eventSlug, gameId.value)),
  queryFn: () => fetchGame(client, props.eventSlug, gameId.value),
  retry: false,
});

// A Game in a Draft Round and one that does not exist both answer 404 here.
const missing = computed(() => error.value instanceof ApiError && error.value.kind === 'not_found');

/** What a Player crossing the hall calls this Game. */
const title = computed(() => {
  if (game.value === undefined) {
    return 'Game';
  }

  return game.value.is_bye ? 'Bye' : `Table ${game.value.table_number}`;
});

/** The sides of the table: two teams, or the one team that drew the Bye. */
const teams = computed<GameAttendee[]>(() => game.value?.attendees ?? []);

const selected = ref(0);

// A Game read after another one starts on the first team rather than on
// whichever side happened to be open at the table before it.
watch(gameId, () => {
  selected.value = 0;
});

const openTeam = computed<GameAttendee | null>(() => teams.value[selected.value] ?? null);

/**
 * Whether this reader is entitled to a list at all.
 *
 * The API omits `army_list` rather than nulling it when a team's lists are
 * closed, so an absent key and an empty list mean different things: one is
 * not yet the reader's business, the other is what the Player submitted.
 */
function isRevealed(member: GameMember): boolean {
  return member.army_list !== undefined;
}

/**
 * Move between tabs on the arrow keys, which is what a tablist owes a reader
 * who is not using a pointer. The ends wrap, so neither arrow ever dead-ends.
 */
function onKeydown(event: KeyboardEvent): void {
  const last = teams.value.length - 1;

  const target = {
    ArrowRight: selected.value === last ? 0 : selected.value + 1,
    ArrowLeft: selected.value === 0 ? last : selected.value - 1,
    Home: 0,
    End: last,
  }[event.key];

  if (target === undefined) {
    return;
  }

  event.preventDefault();
  selected.value = target;
  document.getElementById(tabId(teams.value[target] as GameAttendee))?.focus();
}

function tabId(team: GameAttendee): string {
  return `army-list-tab-${team.id}`;
}

function panelId(team: GameAttendee): string {
  return `army-list-panel-${team.id}`;
}
</script>

<template>
  <main class="mx-auto flex w-full max-w-md flex-col gap-4 p-5">
    <p
      v-if="isPending"
      class="text-muted-foreground-1"
    >
      Loading the game…
    </p>

    <MissingNotice
      v-else-if="missing"
      thing="game"
    />

    <p
      v-else-if="error"
      data-testid="game-error"
      role="alert"
      class="text-destructive"
    >
      {{ (error as ApiError).message }}
    </p>

    <template v-else-if="game">
      <!-- The way back and the name of the place travel together: the table
           number is a label on the scoreline below it rather than a title the
           screen has to be introduced by. They are not tight against each
           other, though — a pill sitting directly under the link reads as
           part of it rather than as the thing it leads to. -->
      <div class="flex flex-col gap-5">
        <!-- The Rounds tab reaches the Round, not the Game beneath it, so this
             screen keeps the back link the Round screen does without. -->
        <RouterLink
          :to="{ name: 'round', params: { eventSlug: props.eventSlug, roundId: game.round.id } }"
          data-testid="back-to-round"
          class="inline-flex items-center gap-x-1 self-start text-sm font-medium text-muted-foreground-1 hover:text-foreground focus:text-foreground focus:outline-hidden"
        >
          <ChevronLeft class="size-4 shrink-0" />
          Back to {{ roundTitle(game.round) }}
        </RouterLink>

        <!-- Both facts wear the same pill the Round's rows do, so tapping a
             row lands on the thing that was tapped. The table number stays the
             screen's heading — a deep link has to land somewhere named — it
             simply is not dressed as a title, because the scoreline under it
             is what the screen is for. -->
        <header class="flex items-center justify-between gap-3">
          <h1
            data-testid="game-name"
            class="game-label"
          >
            {{ title }}
          </h1>

          <span
            v-if="game.result.submitted_at"
            data-testid="game-finished"
            class="game-label shrink-0"
          >
            Finished
          </span>
        </header>
      </div>

      <!-- The same table the Round screen lists, opened out: every column the
           Event scores on rather than the one it leads with. Nothing is drawn
           around it, so it reads as the top of this screen rather than as a
           card restating the one that was tapped. -->
      <div
        data-testid="game-scoreline"
        class="border-b border-card-divider pb-4"
      >
        <GameScoreTable
          :attendees="game.attendees"
          :columns="game.score_types"
        />
      </div>

      <p
        v-if="teams.length === 0"
        data-testid="teams-empty"
        class="text-muted-foreground-1"
      >
        Nobody is down to play this game yet.
      </p>

      <section
        v-else
        class="flex flex-col gap-4"
      >
        <h2 class="sr-only">
          Army lists
        </h2>

        <!-- The sides split the width evenly rather than each taking what
             its name happens to need: two tabs of the same size are two
             halves of a table, and a long team name would otherwise push its
             opponent's into a corner of the screen. A name too long for its
             half is truncated — it is already on the scoreline above, and
             both the tooltip and the panel below give it back in full —
             because the alternatives are wrapping to a second row, which
             moves the lists every time a Player changes side, or scrolling,
             which hides one of only two tabs. -->
        <div
          role="tablist"
          aria-label="Army lists"
          data-testid="army-list-tabs"
          class="flex border-b border-card-line"
          @keydown="onKeydown"
        >
          <!-- A tablist is a single tab stop: only the open tab is reachable
               with Tab, and the arrows move between them. -->
          <button
            v-for="(team, index) in teams"
            :id="tabId(team)"
            :key="team.id"
            type="button"
            role="tab"
            :data-testid="`army-list-tab-${team.id}`"
            :aria-selected="index === selected"
            :aria-controls="panelId(team)"
            :tabindex="index === selected ? 0 : -1"
            :title="team.name"
            class="min-w-0 flex-1 truncate border-b-2 px-3 py-2 text-sm font-medium focus:outline-hidden"
            :class="index === selected
              ? 'border-primary text-foreground'
              : 'border-transparent text-muted-foreground-1 hover:text-foreground focus:text-foreground'"
            @click="selected = index"
          >
            {{ team.name }}
          </button>
        </div>

        <div
          v-if="openTeam"
          :id="panelId(openTeam)"
          :key="openTeam.id"
          role="tabpanel"
          :aria-labelledby="tabId(openTeam)"
          :data-testid="`army-list-panel-${openTeam.id}`"
          tabindex="0"
          class="flex flex-col gap-3 focus:outline-hidden"
        >
          <!-- Every Player on the side that is open, each in their own card:
               a doubles team is two lists, and they are read one after the
               other rather than looked up one at a time. -->
          <article
            v-for="member in openTeam.members"
            :key="member.id"
            :data-testid="`member-${member.id}`"
            class="flex flex-col gap-0.5 rounded-xl border border-card-line bg-card px-4 py-3.5 shadow-2xs"
          >
            <p class="text-base font-semibold text-foreground">
              {{ member.name }}
            </p>
            <p
              data-testid="member-faction"
              class="text-sm"
              :class="member.faction ? 'text-muted-foreground-1' : 'text-muted-foreground'"
            >
              {{ member.faction?.name ?? 'Faction not chosen' }}
            </p>

            <!-- Closed lists are said to be closed. A blank where a list would
                 be reads as a Player who never wrote one. -->
            <p
              v-if="!isRevealed(member)"
              :data-testid="`army-list-closed-${member.id}`"
              class="mt-2 text-sm text-muted-foreground"
            >
              {{ member.army_list_locked
                ? 'This list is in, and opens once every player on the team has submitted.'
                : 'This list has not been submitted yet.' }}
            </p>

            <p
              v-else
              :data-testid="`army-list-${member.id}`"
              class="mt-2 whitespace-pre-wrap text-sm"
              :class="member.army_list ? 'text-foreground' : 'text-muted-foreground'"
            >
              {{ member.army_list || 'No list was written.' }}
            </p>
          </article>

          <p
            v-if="openTeam.members.length === 0"
            data-testid="team-members-empty"
            class="text-muted-foreground-1"
          >
            Nobody has been named for this team yet.
          </p>
        </div>
      </section>
    </template>
  </main>
</template>
