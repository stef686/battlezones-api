<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query';
import { computed, ref } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import type { ApiError } from '@/api/errors';
import { fetchEvent } from '@/api/events';
import { keys } from '@/api/keys';
import { columnLabel } from '@/api/rounds';
import { columnsOf, factionsOf, fetchStandings, scoreOf, type Standing, type StandingColumn } from '@/api/standings';
import StandingMovement from '@/components/StandingMovement.vue';
import TeamAvatar from '@/components/TeamAvatar.vue';
import TextField from '@/components/TextField.vue';
import { useEventPulse } from '@/composables/useEventPulse';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();

const { data: event } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

// While the Event is being run, a result landing anywhere moves these.
useEventPulse(() => props.eventSlug, computed(() => event.value?.status === 'active'));

const { data, isPending, error } = useQuery({
  queryKey: computed(() => keys.standings(props.eventSlug)),
  queryFn: () => fetchStandings(client, props.eventSlug),
});

const all = computed(() => data.value ?? []);

/**
 * The search filters the table already in hand rather than asking the API per
 * keystroke, exactly as the Round screen's does: a field is a few hundred
 * teams at most, they have all arrived, and a hall's wifi is the wrong place
 * to spend a request on every letter.
 */
const search = ref('');

const standings = computed(() => all.value.filter(matchesSearch));

const nothingMatched = computed(() => all.value.length > 0 && standings.value.length === 0);

/**
 * The columns the Event is scored on, read off the Standings rather than
 * named here: an Event declares its own Score Types, exactly as a Round's
 * Games do, and a table that knows only Match Points and Victory Points shows
 * two columns of dashes to every Event scored on anything else.
 */
const columns = computed<StandingColumn[]>(() => columnsOf(all.value));

/**
 * A row with what it takes to draw it worked out once: the Factions are read
 * twice — to decide whether the line is there at all, and to fill it — and a
 * template calls a function again every time it names it.
 */
const rows = computed(() => standings.value.map((standing) => ({
  standing,
  factions: factionsOf(standing),
  scores: columns.value.map((column) => ({ slug: column.slug, value: scoreOf(standing, column.slug) })),
})));

function matchesSearch(standing: Standing): boolean {
  const term = search.value.trim().toLowerCase();

  return term === '' || standing.attendee.name.toLowerCase().includes(term);
}
</script>

<template>
  <main class="mx-auto flex w-full max-w-md flex-col gap-5 p-5">
    <!-- The nav names this screen, so the heading is for a screen reader
         landing here from a deep link and costs no space. -->
    <h1 class="sr-only">
      Standings
    </h1>

    <p
      v-if="isPending"
      class="text-muted-foreground-1"
    >
      Loading standings…
    </p>
    <p
      v-else-if="error"
      role="alert"
      class="text-destructive"
    >
      {{ (error as ApiError).message }}
    </p>

    <template v-else>
      <!-- The placeholder carries what a label and a hint used to, because this
           is one field on a screen whose whole job is the table beneath it. The
           label is still there for a screen reader, just not on screen. -->
      <TextField
        v-if="all.length > 0"
        v-model="search"
        label="Search by team name"
        label-hidden
        type="search"
        placeholder="Search by team name…"
        testid="standings-search"
      />

      <!-- The same table the Round's Games are drawn in — only the strip
           along the top is filled, and the rows sit on the page's own ground
           — so a Player who reads a scoreline on one screen is not asked to
           learn a second way of reading numbers on the next.

           It runs to both edges rather than sitting in a card, which is the
           one place it parts company with a Game. A Game is one of a stack
           and needs an outline to be one thing among several; the standings
           are the whole screen, and the width a card gives back is width the
           names were being truncated to fit. One rule under the last row is
           all that is left of the card — nothing above the headings, which
           the search field already sits clear of.

           The columns at either end carry the page's own inset rather than
           the table's, so the position and the last score line up with the
           field above them; only the columns between stay tight. -->
      <div
        v-if="!nothingMatched"
        class="-mx-5 border-b border-card-line"
      >
        <table
          data-testid="standings"
          class="w-full"
        >
          <thead>
            <tr class="bg-background-1 text-2xs uppercase tracking-widest text-muted-foreground">
              <th
                scope="col"
                class="py-2 pl-5 pr-2 text-start font-bold"
              >
                #
              </th>
              <!-- The names below need no heading, and the empty cell takes the
                   width the position and the scores do not. -->
              <th
                scope="col"
                class="w-full px-2 py-2 text-start font-medium"
              >
                <span class="sr-only">Attendee</span>
              </th>
              <!-- Initials over the numbers, with the name behind them for a
                   screen reader, which has no run of rows to read the heading
                   against. The last column carries the page's own inset. -->
              <th
                v-for="(column, index) in columns"
                :key="column.slug"
                :data-testid="`column-${column.slug}`"
                scope="col"
                class="py-2 text-center font-bold whitespace-nowrap"
                :class="index === columns.length - 1 ? 'pl-2 pr-5' : 'px-2'"
              >
                {{ columnLabel(column) }}
                <span class="sr-only">{{ column.name }}</span>
              </th>
            </tr>
          </thead>

          <tbody class="divide-y divide-card-divider border-t border-card-divider">
            <tr
              v-for="row in rows"
              :key="row.standing.id"
              :data-testid="`standing-${row.standing.attendee.id}`"
            >
              <!-- The arrow sits with the position rather than in a column of
                   its own: it is a fact about that number, and a phone has no
                   width to spend on a column that is empty for a whole first
                   round. -->
              <td class="py-3 pl-5 pr-2 align-top text-2xs tabular-nums whitespace-nowrap text-muted-foreground-1">
                <span class="flex items-center gap-1.5">
                  {{ row.standing.position }}
                  <StandingMovement
                    :data-testid="`movement-${row.standing.attendee.id}`"
                    :movement="row.standing.movement"
                  />
                </span>
              </td>
              <!-- The name is cut rather than allowed to push the numbers off a
                   phone: a table that scrolls sideways hides the scores that
                   are the whole point of reading it. -->
              <th
                scope="row"
                class="max-w-0 align-top text-start text-2xs font-normal text-foreground"
              >
                <!-- A row cannot be a link, so the name is, and it carries
                     the cell's padding rather than the cell so the whole
                     width of it is the target rather than the text alone. A
                     reader scanning the table for a team is a reader who
                     wants that team, and the Attendees tab is a long way
                     round to reach it. -->
                <RouterLink
                  :to="{ name: 'attendee', params: { eventSlug: props.eventSlug, attendeeId: row.standing.attendee.id } }"
                  :data-testid="`open-attendee-${row.standing.attendee.id}`"
                  class="block px-2 py-3 hover:bg-muted-hover focus:bg-muted-hover focus:outline-hidden"
                >
                  <!-- The name keeps the step the rest of the table gave up:
                       it is what a reader is scanning for, and the numbers
                       and the Factions are what it is read against. The badge
                       rides beside it at the smallest size the table draws,
                       because width here comes out of the name. -->
                  <span class="flex min-w-0 items-center gap-1.5">
                    <TeamAvatar
                      :name="row.standing.attendee.name"
                      :src="row.standing.attendee.avatar"
                      size="xs"
                    />
                    <span
                      :data-testid="`name-${row.standing.attendee.id}`"
                      class="block truncate text-xs"
                    >{{ row.standing.attendee.name }}</span>
                  </span>

                  <!-- Under the name and in grey, because it qualifies the
                       team rather than identifying it: two teams called
                       something forgettable are told apart by what they
                       brought. Truncated on its own line so a doubles pair
                       cannot push the scores off a phone. -->
                  <span
                    v-if="row.factions !== ''"
                    :data-testid="`factions-${row.standing.attendee.id}`"
                    class="block truncate text-muted-foreground"
                  >
                    {{ row.factions }}
                  </span>
                </RouterLink>
              </th>
              <td
                v-for="(cell, index) in row.scores"
                :key="cell.slug"
                :data-testid="cell.slug"
                class="py-3 align-top text-center text-2xs font-medium tabular-nums whitespace-nowrap text-foreground"
                :class="index === row.scores.length - 1 ? 'pl-2 pr-5' : 'px-2'"
              >
                {{ cell.value }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Naming the term is what stops an empty table reading as an Event
           nobody has scored in yet. -->
      <p
        v-else
        data-testid="standings-unmatched"
        class="text-muted-foreground-1"
      >
        No team in this event matches “{{ search.trim() }}”.
      </p>
    </template>
  </main>
</template>
