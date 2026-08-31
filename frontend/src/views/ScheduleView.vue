<script setup lang="ts">
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, ref, watch } from 'vue';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { addScheduleBlock, fetchEvent, fetchSchedule } from '@/api/events';
import { keys } from '@/api/keys';
import { fetchRounds, roundTitle } from '@/api/rounds';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import MissingNotice from '@/components/MissingNotice.vue';
import SelectField from '@/components/SelectField.vue';
import TabStrip from '@/components/TabStrip.vue';
import TextField from '@/components/TextField.vue';
import { dayInZone, eventTimestamp, shortDay, wallClockTime } from '@/lib/dates';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();
const queryClient = useQueryClient();

const { data: event } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

const mayOrganise = computed(() => event.value?.viewer?.permissions.organise === true);

/** Only an Organiser adds a block, and only a round block needs the Rounds. */
const { data: rounds } = useQuery({
  queryKey: computed(() => keys.rounds(props.eventSlug)),
  queryFn: () => fetchRounds(client, props.eventSlug),
  enabled: mayOrganise,
  retry: false,
});

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

/**
 * Adding a block, at the foot of the day it is being added to.
 *
 * Folded away until it is asked for: an Organiser reads this screen far more
 * often than they write to it, and a form under every day would be the first
 * thing a Player-facing screen showed the person running the Event.
 */
const adding = ref(false);

const BLOCK_TYPES = [
  { value: 'info', label: 'Information' },
  { value: 'round', label: 'Round' },
  { value: 'painting_voting', label: 'Painting voting' },
];

const form = ref({ date: '', starts: '', ends: '', label: '', type: 'info', roundId: '' });

const roundOptions = computed(() => (rounds.value ?? []).map((round) => ({
  value: String(round.id),
  label: roundTitle(round),
})));

/**
 * A new block starts on the day being read, and on the Event's first day when
 * there is no schedule yet to be reading.
 */
function openForm(): void {
  const zone = event.value?.timezone ?? 'UTC';
  const start = event.value?.starts_at;

  form.value = {
    date: openDay.value?.date ?? (start === undefined || start === null ? '' : dayInZone(start, zone)),
    starts: '',
    ends: '',
    label: '',
    type: 'info',
    roundId: '',
  };

  problem.value = null;
  fieldErrors.value = {};
  adding.value = true;
}

const saving = ref(false);
const problem = ref<string | null>(null);
const fieldErrors = ref<Record<string, string[]>>({});

function errorsFor(field: string): string[] {
  return fieldErrors.value[field] ?? [];
}

async function add(): Promise<void> {
  saving.value = true;
  problem.value = null;
  fieldErrors.value = {};

  const zone = event.value?.timezone ?? 'UTC';
  const { date, starts, ends, label, type, roundId } = form.value;

  try {
    // The times are the hall's, so they are written with the Event's offset
    // rather than the offset of whoever is typing them.
    await addScheduleBlock(client, props.eventSlug, {
      label,
      type,
      starts_at: eventTimestamp(date, starts, zone),
      ends_at: eventTimestamp(date, ends, zone),
      round_id: type === 'round' && roundId !== '' ? Number(roundId) : null,
    });

    await queryClient.invalidateQueries({ queryKey: keys.schedule(props.eventSlug) });

    // The day it landed on is the day to be looking at, which is not
    // necessarily the day that was open when the form was filled in.
    const landedOn = (days.value ?? []).findIndex((day) => day.date === date);
    selected.value = landedOn === -1 ? selected.value : landedOn;

    adding.value = false;
  } catch (caught) {
    if (caught instanceof ApiError && caught.kind === 'validation') {
      fieldErrors.value = caught.fields;
    } else {
      problem.value = caught instanceof ApiError ? caught.message : 'That could not be added.';
    }
  } finally {
    saving.value = false;
  }
}
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

    <template v-else-if="empty">
      <p
        data-testid="schedule-empty"
        class="text-muted-foreground-1"
      >
        Nothing scheduled yet.
      </p>

      <AppButton
        v-if="mayOrganise && !adding"
        data-testid="add-block"
        variant="secondary"
        class="self-start"
        @click="openForm"
      >
        Add an item
      </AppButton>
    </template>

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

        <!-- At the foot of the day, where the next block would go: an
             Organiser adds to the end of a schedule far more often than into
             the middle of one, and the API takes the times either way. -->
        <button
          v-if="mayOrganise && !adding"
          type="button"
          data-testid="add-block"
          class="flex w-full items-center gap-4 px-5 py-3.5 text-start text-sm font-medium text-primary hover:bg-muted-hover focus:bg-muted-hover focus:outline-hidden"
          @click="openForm"
        >
          Add an item
        </button>
      </div>
    </TabStrip>
    <!-- Outside the tabs, because a block being added may belong to a day the
         schedule does not have yet, and a form that vanished when the reader
         changed tab would take what they had typed with it. -->
    <form
      v-if="adding"
      data-testid="add-block-form"
      class="flex flex-col gap-4"
      novalidate
      @submit.prevent="add"
    >
      <TextField
        v-model="form.label"
        label="What is it?"
        testid="block-label"
        :errors="errorsFor('label')"
      />

      <SelectField
        v-model="form.type"
        label="Kind"
        testid="block-type"
        :options="BLOCK_TYPES"
        hint="A round links the block to the round it runs, which is what lights it as live."
        :errors="errorsFor('type')"
      />

      <SelectField
        v-if="form.type === 'round'"
        v-model="form.roundId"
        label="Which round"
        placeholder="Choose a round"
        testid="block-round"
        :options="roundOptions"
        :errors="errorsFor('round_id')"
      />

      <TextField
        v-model="form.date"
        label="Day"
        type="date"
        testid="block-date"
        :errors="errorsFor('starts_at')"
      />

      <div class="flex gap-3">
        <TextField
          v-model="form.starts"
          label="Starts"
          type="time"
          testid="block-starts"
          class="flex-1"
        />
        <TextField
          v-model="form.ends"
          label="Ends"
          type="time"
          testid="block-ends"
          class="flex-1"
          :errors="errorsFor('ends_at')"
        />
      </div>

      <p class="text-sm text-muted-foreground">
        Times are the ones the hall reads, whatever clock you are typing on.
      </p>

      <AppAlert
        v-if="problem"
        data-testid="add-block-problem"
        tone="error"
      >
        {{ problem }}
      </AppAlert>

      <div class="flex gap-3">
        <AppButton
          type="submit"
          data-testid="save-block"
          :disabled="saving"
          class="flex-1"
        >
          {{ saving ? 'Adding…' : 'Add to the schedule' }}
        </AppButton>
        <AppButton
          data-testid="cancel-block"
          variant="secondary"
          :disabled="saving"
          @click="adding = false"
        >
          Cancel
        </AppButton>
      </div>
    </form>
  </main>
</template>
