<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query';
import { computed } from 'vue';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchSchedule } from '@/api/events';
import { keys } from '@/api/keys';
import MissingNotice from '@/components/MissingNotice.vue';
import { formatDay, wallClockTime } from '@/lib/dates';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();

const { data: days, isPending, error } = useQuery({
  queryKey: computed(() => keys.schedule(props.eventSlug)),
  queryFn: () => fetchSchedule(client, props.eventSlug),
  retry: false,
});

const missing = computed(() => error.value instanceof ApiError && error.value.kind === 'not_found');
const empty = computed(() => days.value !== undefined && days.value.length === 0);
</script>

<template>
  <main class="mx-auto flex w-full max-w-md flex-col gap-6 p-5">
    <h1 class="text-2xl font-bold tracking-tight text-foreground">
      Schedule
    </h1>

    <p
      v-if="isPending"
      class="text-muted-foreground-1"
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
      role="alert"
      class="text-destructive"
    >
      {{ (error as ApiError).message }}
    </p>

    <p
      v-else-if="empty"
      data-testid="schedule-empty"
      class="text-muted-foreground-1"
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
      >
        <h2 class="mb-3 text-xs font-medium uppercase tracking-widest text-muted-foreground">
          {{ formatDay(day.date) }}
        </h2>

        <!-- One card per day, blocks divided inside it: a schedule reads as a
             column, not as a stack of separate things. -->
        <div class="divide-y divide-card-divider overflow-hidden rounded-xl border border-card-line bg-card shadow-2xs">
          <article
            v-for="block in day.blocks"
            :key="block.id"
            :data-testid="`block-${block.id}`"
            class="flex items-baseline gap-4 px-4 py-3.5"
            :class="block.is_target_live ? 'bg-primary/10' : ''"
          >
            <!-- The time as the hall reads it, tabular so the column lines up
                 down the page rather than jittering with the digits. -->
            <time
              :datetime="block.starts_at"
              data-testid="block-time"
              class="w-14 shrink-0 text-lg font-semibold tabular-nums text-foreground"
            >
              {{ wallClockTime(block.starts_at) }}
            </time>

            <div class="flex min-w-0 flex-col">
              <p class="truncate text-sm font-medium text-foreground">
                {{ block.label }}
              </p>
              <p class="text-sm text-muted-foreground">
                until {{ wallClockTime(block.ends_at) }}
              </p>
            </div>

            <span
              v-if="block.is_target_live"
              data-testid="block-live"
              class="ms-auto inline-flex shrink-0 items-center rounded-full bg-primary px-2.5 py-1 text-xs font-medium uppercase tracking-wide text-primary-foreground"
            >
              Now
            </span>
          </article>
        </div>
      </section>
    </div>
  </main>
</template>
