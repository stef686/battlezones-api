<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query';
import { computed } from 'vue';

import { useApiClient } from '@/api';
import type { ApiError } from '@/api/errors';

const props = defineProps<{ eventSlug: string }>();

interface Standing {
  id: number;
  position: number;
  attendee: { id: number; name: string };
  scores: { value: number | string; score_type: { slug: string; name: string } }[];
}

const client = useApiClient();

const { data, isPending, error } = useQuery({
  queryKey: ['standings', props.eventSlug],
  queryFn: () => client.get<{ data: Standing[] }>(`/api/events/${props.eventSlug}/standings`),
});

const standings = computed(() => data.value?.data ?? []);

function score(standing: Standing, slug: string): string {
  const found = standing.scores.find((entry) => entry.score_type.slug === slug);

  return found === undefined ? '—' : String(Number(found.value));
}
</script>

<template>
  <main class="mx-auto flex min-h-screen w-full max-w-md flex-col gap-5 p-5">
    <h1 class="text-xl font-semibold text-ink">
      Standings
    </h1>

    <p
      v-if="isPending"
      class="text-ink-muted"
    >
      Loading standings…
    </p>
    <p
      v-else-if="error"
      class="text-danger"
    >
      {{ (error as ApiError).message }}
    </p>

    <table
      v-else
      data-testid="standings"
      class="w-full border-collapse text-left"
    >
      <thead>
        <tr class="text-xs uppercase tracking-widest text-ink-faint">
          <th class="py-2 font-medium">
            #
          </th>
          <th class="py-2 font-medium">
            Attendee
          </th>
          <th class="py-2 text-right font-medium">
            MP
          </th>
          <th class="py-2 text-right font-medium">
            VP
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="standing in standings"
          :key="standing.id"
          class="border-t border-border"
          :data-testid="`standing-${standing.attendee.id}`"
        >
          <td class="py-3 tabular-nums text-ink-muted">
            {{ standing.position }}
          </td>
          <td class="py-3 text-ink">
            {{ standing.attendee.name }}
          </td>
          <td
            class="py-3 text-right tabular-nums text-ink"
            data-testid="match-points"
          >
            {{ score(standing, 'match-points') }}
          </td>
          <td
            class="py-3 text-right tabular-nums text-ink"
            data-testid="victory-points"
          >
            {{ score(standing, 'victory-points') }}
          </td>
        </tr>
      </tbody>
    </table>
  </main>
</template>
