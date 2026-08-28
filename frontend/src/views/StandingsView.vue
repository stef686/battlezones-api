<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query';
import { computed, ref } from 'vue';

import { useApiClient } from '@/api';
import type { ApiError } from '@/api/errors';
import { fetchEvent } from '@/api/events';
import { keys } from '@/api/keys';
import { fetchStandings, scoreOf, type Standing } from '@/api/standings';
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

function matchesSearch(standing: Standing): boolean {
  const term = search.value.trim().toLowerCase();

  return term === '' || standing.attendee.name.toLowerCase().includes(term);
}

function score(standing: Standing, slug: string): string {
  return scoreOf(standing, slug);
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

      <!-- The same card and the same table the Round's Games are drawn in, so
           a Player who reads a scoreline on one screen is not asked to learn a
           second way of reading numbers on the next. Only the strip along the
           top is filled; the rows sit on the page's own ground, which is what
           gives the table its rhythm. -->
      <div
        v-if="!nothingMatched"
        class="overflow-hidden rounded-xl border border-card-line shadow-2xs"
      >
        <table
          data-testid="standings"
          class="w-full"
        >
          <thead>
            <tr class="bg-background-1 text-xs uppercase tracking-widest text-muted-foreground">
              <th
                scope="col"
                class="px-3 py-2 text-start font-bold"
              >
                #
              </th>
              <!-- The names below need no heading, and the empty cell takes the
                   width the position and the scores do not. -->
              <th
                scope="col"
                class="w-full px-3 py-2 text-start font-medium"
              >
                <span class="sr-only">Attendee</span>
              </th>
              <th
                scope="col"
                class="px-3 py-2 text-center font-bold whitespace-nowrap"
              >
                MP
                <span class="sr-only">Match Points</span>
              </th>
              <th
                scope="col"
                class="px-3 py-2 text-center font-bold whitespace-nowrap"
              >
                VP
                <span class="sr-only">Victory Points</span>
              </th>
            </tr>
          </thead>

          <tbody class="divide-y divide-card-divider border-t border-card-divider">
            <tr
              v-for="standing in standings"
              :key="standing.id"
              :data-testid="`standing-${standing.attendee.id}`"
            >
              <td class="px-3 py-2 text-xs tabular-nums whitespace-nowrap text-muted-foreground-1">
                {{ standing.position }}
              </td>
              <!-- The name is cut rather than allowed to push the numbers off a
                   phone: a table that scrolls sideways hides the scores that
                   are the whole point of reading it. -->
              <th
                scope="row"
                class="max-w-0 px-3 py-2 text-start text-xs font-normal text-foreground"
              >
                <span class="block truncate">{{ standing.attendee.name }}</span>
              </th>
              <td
                class="px-3 py-2 text-center text-xs font-medium tabular-nums whitespace-nowrap text-foreground"
                data-testid="match-points"
              >
                {{ score(standing, 'match-points') }}
              </td>
              <td
                class="px-3 py-2 text-center text-xs font-medium tabular-nums whitespace-nowrap text-foreground"
                data-testid="victory-points"
              >
                {{ score(standing, 'victory-points') }}
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
