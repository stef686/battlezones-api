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
import { fetchScoreTypes, replaceScoreTypes } from '@/api/score-types';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import BackLink from '@/components/BackLink.vue';
import EventShapeFields from '@/components/EventShapeFields.vue';
import MissingNotice from '@/components/MissingNotice.vue';
import ScoreTypeListEditor from '@/components/ScoreTypeListEditor.vue';
import {
  errorsUnder,
  scoreTypeChangeOf,
  scoreTypeRowOf,
  type EventShape,
  type ScoreTypeRow,
} from '@/lib/event-fields';

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
 * The Game System and the party size are the shape every entry is built at, so
 * the API refuses both once somebody has entered. Unknown means locked: an
 * Event whose count did not come back is not one to gamble on.
 */
const entered = computed(() => event.value?.attendees_count ?? null);
const shapeLocked = computed(() => entered.value !== 0);

const { data: scoreTypes } = useQuery({
  queryKey: computed(() => keys.scoreTypes(props.eventSlug)),
  queryFn: () => fetchScoreTypes(client, props.eventSlug),
  enabled: mayOrganise,
  retry: false,
});

const rows = ref<ScoreTypeRow[] | null>(null);
const savedRows = ref<string | null>(null);

watch(scoreTypes, (loaded) => {
  if (loaded === undefined) {
    return;
  }

  // Same rule as the Event fields below: a background refetch never replaces
  // an edit in progress.
  if (rows.value === null || scoringDirty.value === false) {
    rows.value = loaded.map(scoreTypeRowOf);
    savedRows.value = JSON.stringify(rows.value);
  }
}, { immediate: true });

const scoringDirty = computed(() => rows.value !== null && JSON.stringify(rows.value) !== savedRows.value);

const savingScoring = ref(false);
const scoringProblem = ref<string | null>(null);
const scoringErrors = ref<Record<string, string[]>>({});
const scoringDone = ref(false);

/** The API keys a row's messages under the body's `score_types`; the editor keys them by row. */
const rowErrors = computed(() => errorsUnder(scoringErrors.value, 'score_types'));

async function saveScoring(): Promise<void> {
  if (rows.value === null || savingScoring.value) {
    return;
  }

  savingScoring.value = true;
  scoringProblem.value = null;
  scoringErrors.value = {};
  scoringDone.value = false;

  try {
    const updated = await replaceScoreTypes(client, props.eventSlug, rows.value.map(scoreTypeChangeOf));

    queryClient.setQueryData(keys.scoreTypes(props.eventSlug), updated);
    rows.value = updated.map(scoreTypeRowOf);
    savedRows.value = JSON.stringify(rows.value);
    scoringDone.value = true;
  } catch (caught) {
    if (caught instanceof ApiError && caught.kind === 'validation') {
      scoringErrors.value = caught.fields;
    } else {
      scoringProblem.value = caught instanceof ApiError ? caught.message : 'That could not be saved.';
    }
  } finally {
    savingScoring.value = false;
  }
}

function formOf(loaded: EventSummary): EventShape {
  return {
    max_attendees: loaded.max_attendees === null ? '' : String(loaded.max_attendees),
    game_system_id: loaded.game_system === null ? '' : String(loaded.game_system.id),
    attendee_size: String(loaded.attendee_size),
  };
}

const form = ref<EventShape | null>(null);
const saved = ref<EventShape | null>(null);

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

  // Never sent once the shape is locked: the API would refuse them, and the
  // fields are read-only anyway.
  if (!shapeLocked.value && current.game_system_id !== original.game_system_id && current.game_system_id !== '') {
    changed.game_system_id = Number(current.game_system_id);
  }

  if (!shapeLocked.value && current.attendee_size !== original.attendee_size) {
    changed.attendee_size = Number(current.attendee_size);
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
      <BackLink
        :to="{ name: 'organise', params: { eventSlug: props.eventSlug } }"
        testid="back-to-organise"
      >
        Back to running the event
      </BackLink>

      <header>
        <h1 class="text-2xl font-bold tracking-tight text-foreground">
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
        <EventShapeFields
          v-model="form"
          :locked="shapeLocked"
          :errors="errors"
        />

        <AppButton
          type="submit"
          :disabled="!dirty || saving"
          block
        >
          {{ saving ? 'Saving…' : 'Save' }}
        </AppButton>
      </form>

      <section class="flex flex-col gap-3">
        <h2 class="text-sm font-semibold text-foreground">
          Scoring
        </h2>

        <AppAlert
          v-if="scoringProblem"
          tone="error"
        >
          {{ scoringProblem }}
        </AppAlert>

        <AppAlert
          v-if="scoringDone"
          data-testid="format-scoring-saved"
          tone="success"
        >
          Saved.
        </AppAlert>

        <form
          v-if="rows"
          data-testid="format-scoring-save"
          class="flex flex-col gap-4"
          @submit.prevent="saveScoring"
        >
          <ScoreTypeListEditor
            v-model="rows"
            :errors="rowErrors"
          />

          <p
            v-if="scoringErrors.score_types"
            class="text-sm text-destructive"
          >
            {{ scoringErrors.score_types[0] }}
          </p>

          <AppButton
            type="submit"
            :disabled="!scoringDirty || savingScoring"
            block
          >
            {{ savingScoring ? 'Saving…' : 'Save scoring' }}
          </AppButton>
        </form>
      </section>
    </template>
  </main>
</template>
