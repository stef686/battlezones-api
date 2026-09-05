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

import { Check, ChevronDown, ChevronUp } from 'lucide-vue-next';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchEvent, updateEvent, type EventChanges, type EventSummary } from '@/api/events';
import { fetchGameSystems } from '@/api/game-systems';
import { keys } from '@/api/keys';
import { fetchScoreTypes, replaceScoreTypes, type ScoreType, type ScoreTypeChange } from '@/api/score-types';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import MissingNotice from '@/components/MissingNotice.vue';
import SelectField from '@/components/SelectField.vue';
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
 * The Game System and the party size are the shape every entry is built at, so
 * the API refuses both once somebody has entered. Unknown means locked: an
 * Event whose count did not come back is not one to gamble on.
 */
const entered = computed(() => event.value?.attendees_count ?? null);
const shapeLocked = computed(() => entered.value !== 0);

const { data: gameSystems } = useQuery({
  queryKey: keys.gameSystems(),
  queryFn: () => fetchGameSystems(client),
  enabled: computed(() => mayOrganise.value && !shapeLocked.value),
  retry: false,
});

const gameSystemOptions = computed(() => (gameSystems.value ?? [])
  .map((system) => ({ value: String(system.id), label: system.name })));

/** One Player is singles; anything more is a team of that size. */
const partySizeOptions = Array.from({ length: 8 }, (_, index) => ({
  value: String(index + 1),
  label: index === 0 ? 'Singles — one player' : `Teams of ${index + 1}`,
}));

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

/** A Score Type as the screen edits it: points are text while being typed. */
interface ScoreRow {
  id: number;
  name: string;
  sort_direction: 'asc' | 'desc';
  is_derived: boolean;
  is_primary: boolean;
  counts_for_ranking: boolean;
  win_points: string;
  draw_points: string;
  loss_points: string;
  is_scored: boolean;
}

function rowOf(scoreType: ScoreType): ScoreRow {
  return {
    id: scoreType.id,
    name: scoreType.name,
    sort_direction: scoreType.sort_direction,
    is_derived: scoreType.is_derived,
    is_primary: scoreType.is_primary,
    counts_for_ranking: scoreType.counts_for_ranking,
    win_points: scoreType.win_points ?? '',
    draw_points: scoreType.draw_points ?? '',
    loss_points: scoreType.loss_points ?? '',
    is_scored: scoreType.is_scored,
  };
}

const sourceOptions = [
  { value: 'entered', label: 'Entered by players' },
  { value: 'derived', label: 'Worked out from the result' },
];

const directionOptions = [
  { value: 'desc', label: 'Higher is better' },
  { value: 'asc', label: 'Lower is better' },
];

const rows = ref<ScoreRow[] | null>(null);
const savedRows = ref<string | null>(null);

watch(scoreTypes, (loaded) => {
  if (loaded === undefined) {
    return;
  }

  // Same rule as the Event fields above: a background refetch never replaces
  // an edit in progress.
  if (rows.value === null || scoringDirty.value === false) {
    rows.value = loaded.map(rowOf);
    savedRows.value = JSON.stringify(rows.value);
  }
}, { immediate: true });

const scoringDirty = computed(() => rows.value !== null && JSON.stringify(rows.value) !== savedRows.value);

/** Move a row up or down; its new position is its display and ranking order. */
function move(index: number, by: number): void {
  const current = rows.value;
  const target = index + by;

  if (current === null || target < 0 || target >= current.length) {
    return;
  }

  const [moved] = current.splice(index, 1);

  if (moved !== undefined) {
    current.splice(target, 0, moved);
  }
}

/**
 * Only one row may lead a Game listing, so claiming it takes it off whoever
 * held it — the API refuses two, and being told off for a toggle you can see
 * is worse than the screen just doing the obvious thing.
 */
function claimLead(index: number): void {
  const current = rows.value;

  if (current === null) {
    return;
  }

  const claiming = current[index]?.is_primary !== true;

  current.forEach((row, position) => {
    row.is_primary = claiming && position === index;
  });
}

const savingScoring = ref(false);
const scoringProblem = ref<string | null>(null);
const scoringErrors = ref<Record<string, string[]>>({});
const scoringDone = ref(false);

/** The messages the API keyed to one row's field, if any. */
function rowErrors(index: number, field: string): string[] {
  return scoringErrors.value[`score_types.${index}.${field}`] ?? [];
}

function pointsOf(value: string): number | null {
  return value === '' ? null : Number(value);
}

async function saveScoring(): Promise<void> {
  if (rows.value === null || savingScoring.value) {
    return;
  }

  savingScoring.value = true;
  scoringProblem.value = null;
  scoringErrors.value = {};
  scoringDone.value = false;

  const payload: ScoreTypeChange[] = rows.value.map((row) => ({
    id: row.id,
    name: row.name,
    sort_direction: row.sort_direction,
    is_derived: row.is_derived,
    is_primary: row.is_primary,
    counts_for_ranking: row.counts_for_ranking,
    win_points: row.is_derived ? pointsOf(row.win_points) : null,
    draw_points: row.is_derived ? pointsOf(row.draw_points) : null,
    loss_points: row.is_derived ? pointsOf(row.loss_points) : null,
  }));

  try {
    const updated = await replaceScoreTypes(client, props.eventSlug, payload);

    queryClient.setQueryData(keys.scoreTypes(props.eventSlug), updated);
    rows.value = updated.map(rowOf);
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

interface Form {
  max_attendees: string;
  game_system_id: string;
  attendee_size: string;
}

function formOf(loaded: EventSummary): Form {
  return {
    max_attendees: loaded.max_attendees === null ? '' : String(loaded.max_attendees),
    game_system_id: loaded.game_system === null ? '' : String(loaded.game_system.id),
    attendee_size: String(loaded.attendee_size),
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
        <SelectField
          v-model="form.game_system_id"
          label="Game system"
          :options="gameSystemOptions"
          placeholder="Choose a game"
          :disabled="shapeLocked"
          testid="format-game-system"
          :errors="errors.game_system_id"
        />

        <SelectField
          v-model="form.attendee_size"
          label="Played in"
          :options="partySizeOptions"
          :disabled="shapeLocked"
          testid="format-attendee-size"
          :errors="errors.attendee_size"
        />

        <p
          v-if="shapeLocked"
          data-testid="format-shape-locked"
          class="text-sm text-muted-foreground-1"
        >
          <template v-if="entered !== null">
            The game and the party size are fixed now {{ entered }} parties have entered: every entry
            was built at this size, and every faction chosen belongs to this game.
          </template>
          <template v-else>
            The game and the party size are fixed once anybody has entered.
          </template>
        </p>

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

        <p
          v-if="rows && rows.length === 0"
          data-testid="format-no-score-types"
          class="text-sm text-muted-foreground-1"
        >
          No scoring set up yet.
        </p>

        <form
          v-else-if="rows"
          data-testid="format-scoring-save"
          class="flex flex-col gap-4"
          @submit.prevent="saveScoring"
        >
          <div
            v-for="(row, index) in rows"
            :key="row.id"
            data-testid="format-score-type"
            class="flex flex-col gap-4 rounded-lg border border-card-line bg-card p-4"
          >
            <div class="flex items-start justify-between gap-3">
              <p class="text-xs font-medium uppercase tracking-widest text-muted-foreground">
                Column {{ index + 1 }}
              </p>
              <div class="flex gap-1">
                <button
                  type="button"
                  :data-testid="`score-up-${index}`"
                  :disabled="index === 0"
                  class="rounded-md border border-card-line p-1.5 text-muted-foreground hover:text-foreground disabled:opacity-40"
                  :aria-label="`Move ${row.name} up`"
                  @click="move(index, -1)"
                >
                  <ChevronUp class="size-4" />
                </button>
                <button
                  type="button"
                  :data-testid="`score-down-${index}`"
                  :disabled="index === rows.length - 1"
                  class="rounded-md border border-card-line p-1.5 text-muted-foreground hover:text-foreground disabled:opacity-40"
                  :aria-label="`Move ${row.name} down`"
                  @click="move(index, 1)"
                >
                  <ChevronDown class="size-4" />
                </button>
              </div>
            </div>

            <TextField
              v-model="row.name"
              label="Called"
              :testid="`score-name-${index}`"
              :errors="rowErrors(index, 'name')"
            />

            <SelectField
              :model-value="row.is_derived ? 'derived' : 'entered'"
              label="How it is scored"
              :options="sourceOptions"
              :testid="`score-source-${index}`"
              :errors="rowErrors(index, 'is_derived')"
              @update:model-value="row.is_derived = $event === 'derived'"
            />

            <div
              v-if="row.is_derived"
              class="grid grid-cols-3 gap-3"
            >
              <TextField
                v-model="row.win_points"
                label="Win"
                inputmode="numeric"
                :testid="`score-win-${index}`"
                :errors="rowErrors(index, 'win_points')"
              />
              <TextField
                v-model="row.draw_points"
                label="Draw"
                inputmode="numeric"
                :testid="`score-draw-${index}`"
                :errors="rowErrors(index, 'draw_points')"
              />
              <TextField
                v-model="row.loss_points"
                label="Loss"
                inputmode="numeric"
                :testid="`score-loss-${index}`"
                :errors="rowErrors(index, 'loss_points')"
              />
            </div>

            <SelectField
              v-model="row.sort_direction"
              label="Which way up"
              :options="directionOptions"
              :testid="`score-direction-${index}`"
              :errors="rowErrors(index, 'sort_direction')"
            />

            <div class="flex flex-col gap-2">
              <button
                type="button"
                :data-testid="`score-primary-${index}`"
                :aria-pressed="row.is_primary"
                class="flex items-center justify-between gap-3 rounded-lg border px-4 py-3 text-left text-sm"
                :class="row.is_primary ? 'border-primary text-foreground' : 'border-card-line text-muted-foreground'"
                @click="claimLead(index)"
              >
                <span>Leads a game listing</span>
                <Check
                  v-if="row.is_primary"
                  class="size-4 shrink-0"
                />
              </button>

              <button
                type="button"
                :data-testid="`score-ranking-${index}`"
                :aria-pressed="row.counts_for_ranking"
                class="flex items-center justify-between gap-3 rounded-lg border px-4 py-3 text-left text-sm"
                :class="row.counts_for_ranking ? 'border-primary text-foreground' : 'border-card-line text-muted-foreground'"
                @click="row.counts_for_ranking = !row.counts_for_ranking"
              >
                <span>Counts for ranking</span>
                <Check
                  v-if="row.counts_for_ranking"
                  class="size-4 shrink-0"
                />
              </button>
            </div>

            <p
              v-if="row.is_scored"
              class="text-xs text-muted-foreground-1"
            >
              Games have already been scored on this column.
            </p>
          </div>

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
