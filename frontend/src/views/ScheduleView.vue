<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query';
import { computed } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchSchedule } from '@/api/events';
import MissingNotice from '@/components/MissingNotice.vue';
import { formatDay, wallClockTime } from '@/lib/dates';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();

const { data: days, isPending, error } = useQuery({
  queryKey: ['schedule', props.eventSlug],
  queryFn: () => fetchSchedule(client, props.eventSlug),
  retry: false,
});

const missing = computed(() => error.value instanceof ApiError && error.value.kind === 'not_found');
const empty = computed(() => days.value !== undefined && days.value.length === 0);
</script>

<template>
  <main class="mx-auto flex min-h-screen w-full max-w-md flex-col gap-6 p-5">
    <RouterLink
      :to="{ name: 'event', params: { eventSlug: props.eventSlug } }"
      data-testid="back-to-event"
      class="text-sm text-ink-muted underline underline-offset-4"
    >
      Back to the event
    </RouterLink>

    <h1 class="text-2xl font-semibold tracking-tight text-ink">
      Schedule
    </h1>

    <p
      v-if="isPending"
      class="text-ink-muted"
    >
      Loading the schedule…
    </p>

    <MissingNotice
      v-else-if="missing"
      thing="event"
    />

    <p
      v-else-if="error"
      data-testid="schedule-error"
      class="text-danger"
    >
      {{ (error as ApiError).message }}
    </p>

    <p
      v-else-if="empty"
      data-testid="schedule-empty"
      class="text-ink-muted"
    >
      Nothing scheduled yet.
    </p>

    <div
      v-else
      class="flex flex-col gap-6"
    >
      <section
        v-for="day in days"
        :key="day.date"
        :data-testid="`day-${day.date}`"
        class="flex flex-col gap-3"
      >
        <h2 class="text-sm uppercase tracking-widest text-ink-faint">
          {{ formatDay(day.date) }}
        </h2>

        <article
          v-for="block in day.blocks"
          :key="block.id"
          :data-testid="`block-${block.id}`"
          class="flex items-baseline gap-4 rounded-2xl bg-surface-raised px-4 py-3"
          :class="block.is_target_live ? 'ring-1 ring-accent' : ''"
        >
          <!-- The time as the hall reads it, tabular so the column lines up
               down the page rather than jittering with the digits. -->
          <time
            :datetime="block.starts_at"
            data-testid="block-time"
            class="w-14 shrink-0 text-lg font-semibold tabular-nums text-ink"
          >
            {{ wallClockTime(block.starts_at) }}
          </time>

          <div class="flex min-w-0 flex-col">
            <p class="truncate text-ink">
              {{ block.label }}
            </p>
            <p class="text-sm text-ink-faint">
              until {{ wallClockTime(block.ends_at) }}
            </p>
          </div>

          <span
            v-if="block.is_target_live"
            data-testid="block-live"
            class="ml-auto shrink-0 rounded-md bg-accent px-2 py-0.5 text-xs font-semibold uppercase tracking-wide text-accent-ink"
          >
            Now
          </span>
        </article>
      </section>
    </div>
  </main>
</template>
