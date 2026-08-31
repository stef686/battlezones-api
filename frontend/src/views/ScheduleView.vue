<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query';
import { computed, ref, watch } from 'vue';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchSchedule } from '@/api/events';
import { keys } from '@/api/keys';
import MissingNotice from '@/components/MissingNotice.vue';
import TabStrip from '@/components/TabStrip.vue';
import { shortDay, wallClockTime } from '@/lib/dates';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();

const { data: days, isPending, error } = useQuery({
  queryKey: computed(() => keys.schedule(props.eventSlug)),
  queryFn: () => fetchSchedule(client, props.eventSlug),
  retry: false,
});

const missing = computed(() => error.value instanceof ApiError && error.value.kind === 'not_found');
const empty = computed(() => days.value !== undefined && days.value.length === 0);

/**
 * The days as tabs: the date leads, the weekday follows.
 *
 * A two-day event stacked both days on one screen, which meant scrolling
 * through Saturday to find out when Sunday starts. Tabbed, each day is a
 * screen of its own and the one being played is the one that opens.
 */
const tabs = computed(() => (days.value ?? []).map((day) => ({ id: day.date, name: shortDay(day.date) })));

const selected = ref(0);

/**
 * Open on the day the Event is in rather than on its first.
 *
 * A live block says which day that is without the phone's clock having to
 * agree with the hall's; today's date is the fallback for the hours between
 * Rounds, and the first day for anyone reading the schedule in advance.
 */
watch(days, (loaded) => {
  if (loaded === undefined || loaded.length === 0) {
    return;
  }

  const live = loaded.findIndex((day) => day.blocks.some((block) => block.is_target_live));

  if (live !== -1) {
    selected.value = live;

    return;
  }

  const today = new Date().toLocaleDateString('en-CA');
  const now = loaded.findIndex((day) => day.date === today);

  selected.value = now === -1 ? 0 : now;
}, { immediate: true });

const openDay = computed(() => days.value?.[selected.value] ?? null);
</script>

<template>
  <main class="mx-auto flex w-full max-w-md flex-col gap-6 p-5">
    <!-- The nav names this screen, so the heading is for a screen reader
         landing here from a deep link and costs no space. -->
    <h1 class="sr-only">
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

    <TabStrip
      v-else-if="openDay"
      v-model="selected"
      :items="tabs"
      label="Days"
      id-prefix="day"
    >
      <!-- One rule between blocks and nothing else: a schedule is a column of
           times, and a card around it only sets the day apart from the tab
           that already names it. -->
      <div
        :data-testid="`day-${openDay.date}`"
        class="-mx-5 divide-y divide-card-divider"
      >
        <article
          v-for="block in openDay.blocks"
          :key="block.id"
          :data-testid="`block-${block.id}`"
          class="flex items-center gap-4 px-5 py-3.5"
          :class="block.is_target_live ? 'bg-primary/10' : ''"
        >
          <!-- The time as the hall reads it, tabular so the column lines up
               down the page rather than jittering with the digits. -->
          <time
            :datetime="block.starts_at"
            data-testid="block-time"
            class="w-12 shrink-0 text-base font-semibold tabular-nums text-foreground"
          >
            {{ wallClockTime(block.starts_at) }}
          </time>

          <!-- Only what a block starts. When it ends is the next row's start
               time, and saying it twice spent a line of every row on a phone. -->
          <p class="min-w-0 truncate text-sm font-medium text-foreground">
            {{ block.label }}
          </p>

          <span
            v-if="block.is_target_live"
            data-testid="block-live"
            class="ms-auto inline-flex shrink-0 items-center rounded-full bg-primary px-2.5 py-1 text-xs font-medium uppercase tracking-wide text-primary-foreground"
          >
            Now
          </span>
        </article>

        <p
          v-if="openDay.blocks.length === 0"
          data-testid="day-empty"
          class="px-5 py-3.5 text-muted-foreground-1"
        >
          Nothing scheduled on this day.
        </p>
      </div>
    </TabStrip>
  </main>
</template>
