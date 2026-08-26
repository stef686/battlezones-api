<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query';
import { computed } from 'vue';

import { useApiClient } from '@/api';
import type { ApiError } from '@/api/errors';
import { fetchEvent } from '@/api/events';
import { keys } from '@/api/keys';
import { fetchStandings, scoreOf, type Standing } from '@/api/standings';
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

const standings = computed(() => data.value ?? []);

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

    <!-- The table scrolls inside its own card rather than the page: a long
         Attendee name on a narrow phone must not push the whole screen
         sideways. -->
    <div
      v-else
      class="overflow-hidden rounded-xl border border-card-line bg-card shadow-2xs"
    >
      <div class="overflow-x-auto">
        <table
          data-testid="standings"
          class="min-w-full divide-y divide-card-divider"
        >
          <thead class="bg-card-header">
            <tr class="text-start text-xs uppercase tracking-widest text-muted-foreground">
              <th
                scope="col"
                class="px-4 py-3 text-start font-medium"
              >
                #
              </th>
              <th
                scope="col"
                class="px-4 py-3 text-start font-medium"
              >
                Attendee
              </th>
              <th
                scope="col"
                class="px-4 py-3 text-end font-medium"
              >
                MP
              </th>
              <th
                scope="col"
                class="px-4 py-3 text-end font-medium"
              >
                VP
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-card-divider">
            <tr
              v-for="standing in standings"
              :key="standing.id"
              :data-testid="`standing-${standing.attendee.id}`"
            >
              <td class="whitespace-nowrap px-4 py-3 text-sm tabular-nums text-muted-foreground-1">
                {{ standing.position }}
              </td>
              <td class="px-4 py-3 text-sm font-medium text-foreground">
                {{ standing.attendee.name }}
              </td>
              <td
                class="whitespace-nowrap px-4 py-3 text-end text-sm tabular-nums text-foreground"
                data-testid="match-points"
              >
                {{ score(standing, 'match-points') }}
              </td>
              <td
                class="whitespace-nowrap px-4 py-3 text-end text-sm tabular-nums text-foreground"
                data-testid="victory-points"
              >
                {{ score(standing, 'victory-points') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</template>
