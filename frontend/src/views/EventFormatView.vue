<script setup lang="ts">
/**
 * The shape an Event is run at, as its Organiser can change it.
 *
 * Kept apart from Event settings on purpose: name, dates and venue are what an
 * Event is called and where it happens, while this screen is what it *is* —
 * how many places it has, and in later slices the Game System, the party size
 * and the Score Types it is ranked on.
 */
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, ref, watch } from 'vue';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchEvent, updateEvent, type EventChanges, type EventSummary } from '@/api/events';
import { keys } from '@/api/keys';
import { fetchScoreTypes, type ScoreType } from '@/api/score-types';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import MissingNotice from '@/components/MissingNotice.vue';
import TextField from '@/components/TextField.vue';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();
const queryClient = useQueryClient();

const { data: event, isPending } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

/**
 * A reader without the permission is told the page is not there — the same
 * answer as an Event that does not exist, and for the same reason.
 */
const mayOrganise = computed(() => event.value?.viewer?.permissions.organise === true);
const forbidden = computed(() => event.value !== undefined && !mayOrganise.value);

/**
 * The scoring is read here and edited in a later slice: an Organiser needs to
 * see what their Players will be asked for before they can change it.
 */
const { data: scoreTypes } = useQuery({
  queryKey: computed(() => keys.scoreTypes(props.eventSlug)),
  queryFn: () => fetchScoreTypes(client, props.eventSlug),
  enabled: mayOrganise,
  retry: false,
});

/** How a Score Type is filled in: the platform works it out, or a Player types it. */
function sourceOf(scoreType: ScoreType): string {
  return scoreType.is_derived ? 'Worked out for them' : 'Entered by players';
}

/** Which way up a Score Type ranks, said the way an Organiser would say it. */
function directionOf(scoreType: ScoreType): string {
  return scoreType.sort_direction === 'asc' ? 'Lower is better' : 'Higher is better';
}

/** The parts an Organiser has toggled on, as short badges. */
function marksOf(scoreType: ScoreType): string[] {
  const marks: string[] = [];

  if (scoreType.is_primary) {
    marks.push('Leads a game listing');
  }

  if (scoreType.counts_for_ranking) {
    marks.push('Counts for ranking');
  }

  if (scoreType.is_scored) {
    marks.push('Already scored');
  }

  return marks;
}

interface Form {
  max_attendees: string;
}

function formOf(loaded: EventSummary): Form {
  return {
    max_attendees: loaded.max_attendees === null ? '' : String(loaded.max_attendees),
  };
}

const form = ref<Form | null>(null);
const saved = ref<Form | null>(null);

watch(event, (loaded) => {
  if (loaded === undefined) {
    return;
  }

  // Only while the form is untouched: an Organiser mid-edit must not have
  // their typing replaced by a background refetch.
  if (form.value === null || dirty.value === false) {
    form.value = formOf(loaded);
    saved.value = formOf(loaded);
  }
}, { immediate: true });

const changes = computed<EventChanges>(() => {
  const current = form.value;
  const original = saved.value;

  if (current === null || original === null) {
    return {};
  }

  const changed: EventChanges = {};

  if (current.max_attendees !== original.max_attendees) {
    changed.max_attendees = current.max_attendees === '' ? null : Number(current.max_attendees);
  }

  return changed;
});

const dirty = computed(() => Object.keys(changes.value).length > 0);

const saving = ref(false);
const problem = ref<string | null>(null);
const errors = ref<Record<string, string[]>>({});
const done = ref(false);

async function save(): Promise<void> {
  if (form.value === null || saving.value) {
    return;
  }

  saving.value = true;
  problem.value = null;
  errors.value = {};
  done.value = false;

  try {
    const updated = await updateEvent(client, props.eventSlug, changes.value);

    queryClient.setQueryData(keys.event(props.eventSlug), updated);
    saved.value = formOf(updated);
    form.value = formOf(updated);
    done.value = true;
  } catch (caught) {
    if (caught instanceof ApiError && caught.kind === 'validation') {
      errors.value = caught.fields;
      problem.value = null;
    } else {
      problem.value = caught instanceof ApiError ? caught.message : 'That could not be saved.';
    }
  } finally {
    saving.value = false;
  }
}
</script>

<template>
  <main class="mx-auto flex w-full max-w-md flex-col gap-5 p-5">
    <p
      v-if="isPending"
      class="text-muted-foreground-1"
    >
      Loading…
    </p>

    <MissingNotice
      v-else-if="forbidden || event === undefined"
      thing="page"
    />

    <template v-else-if="form">
      <header>
        <p class="text-xs font-medium uppercase tracking-widest text-muted-foreground">
          {{ event.name }}
        </p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">
          Event format
        </h1>
      </header>

      <AppAlert
        v-if="problem"
        tone="error"
      >
        {{ problem }}
      </AppAlert>

      <AppAlert
        v-if="done"
        data-testid="format-saved"
        tone="success"
      >
        Saved.
      </AppAlert>

      <form
        data-testid="format-save"
        class="flex flex-col gap-5"
        @submit.prevent="save"
      >
        <TextField
          v-model="form.max_attendees"
          label="Places"
          inputmode="numeric"
          hint="Leave empty for no limit. Never fewer than have already entered."
          testid="format-max-attendees"
          :errors="errors.max_attendees"
        />

        <AppButton
          type="submit"
          :disabled="!dirty || saving"
          block
        >
          {{ saving ? 'Saving…' : 'Save format' }}
        </AppButton>
      </form>

      <section class="flex flex-col gap-3">
        <h2 class="text-sm font-semibold text-foreground">
          Scoring
        </h2>

        <p
          v-if="scoreTypes && scoreTypes.length === 0"
          data-testid="format-no-score-types"
          class="text-sm text-muted-foreground-1"
        >
          No scoring set up yet.
        </p>

        <ul
          v-else-if="scoreTypes"
          class="flex flex-col gap-2"
        >
          <li
            v-for="scoreType in scoreTypes"
            :key="scoreType.id"
            data-testid="format-score-type"
            class="rounded-lg border border-card-line bg-card p-4"
          >
            <p class="text-sm font-medium text-foreground">
              {{ scoreType.name }}
            </p>
            <p class="mt-1 text-xs text-muted-foreground-1">
              {{ sourceOf(scoreType) }} · {{ directionOf(scoreType) }}
            </p>
            <ul
              v-if="marksOf(scoreType).length"
              class="mt-2 flex flex-wrap gap-2"
            >
              <li
                v-for="mark in marksOf(scoreType)"
                :key="mark"
                class="rounded-full bg-secondary px-2 py-1 text-xs text-secondary-foreground"
              >
                {{ mark }}
              </li>
            </ul>
          </li>
        </ul>
      </section>
    </template>
  </main>
</template>
