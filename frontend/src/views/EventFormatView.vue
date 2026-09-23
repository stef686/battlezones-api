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

import { Check, ChevronDown, ChevronUp, Trash2, TriangleAlert } from 'lucide-vue-next';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchEvent, updateEvent, type EventChanges, type EventSummary } from '@/api/events';
import { fetchGameSystems } from '@/api/game-systems';
import { keys } from '@/api/keys';
import { columnLabel } from '@/api/rounds';
import { fetchScoreTypes, replaceScoreTypes, type ScoreType, type ScoreTypeChange } from '@/api/score-types';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import BackLink from '@/components/BackLink.vue';
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
  /** Null on a column being added, which the API creates. */
  id: number | null;
  /** Stable for the life of the row, so a reorder does not remount its inputs. */
  key: string;
  name: string;
  /** Blank means the API works one out from the name. */
  abbreviation: string;
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
    key: `saved-${scoreType.id}`,
    name: scoreType.name,
    abbreviation: scoreType.abbreviation,
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

let added = 0;

/** A blank column, ready to be named. */
function blankRow(): ScoreRow {
  return {
    id: null,
    key: `new-${++added}`,
    name: '',
    abbreviation: '',
    sort_direction: 'desc',
    is_derived: false,
    is_primary: false,
    counts_for_ranking: true,
    win_points: '',
    draw_points: '',
    loss_points: '',
    is_scored: false,
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

/**
 * The keys of the columns drawn open.
 *
 * An Event's scoring is set up once and read past every time after that, so
 * the set arrives folded and an Organiser opens the one they came for. A
 * column they add is opened for them — it is blank, and there is nothing to
 * read on a card whose fields are all empty. Keys rather than indexes, so
 * folding one and reordering the set does not unfold somebody else.
 */
const expanded = ref<Set<string>>(new Set());

function toggle(key: string): void {
  if (expanded.value.has(key)) {
    expanded.value.delete(key);
  } else {
    expanded.value.add(key);
  }

  expanded.value = new Set(expanded.value);
}

/**
 * A folded column with something to answer for is unfolded by the screen: a
 * rejected field that cannot be seen is a form that will not save and will not
 * say why.
 */
function open(index: number, key: string): boolean {
  return expanded.value.has(key) || rowRejected(index);
}

function rowRejected(index: number): boolean {
  return Object.keys(scoringErrors.value).some((field) => field.startsWith(`score_types.${index}.`));
}

/** What a folded column says about itself, under its name. */
function summaryOf(row: ScoreRow): string {
  const source = row.is_derived ? 'Worked out from the result' : 'Entered by players';
  const direction = row.sort_direction === 'desc' ? 'higher is better' : 'lower is better';

  return `${source} · ${direction}`;
}

/**
 * The heading a folded column says it will be shown under, which is the
 * Organiser's own where they wrote one and the API's answer where they did
 * not — worked out here so the card reads the same before a save as after it.
 */
function headingOf(row: ScoreRow): string {
  return columnLabel({ name: row.name, abbreviation: row.abbreviation });
}

function addRow(): void {
  const row = blankRow();

  rows.value?.push(row);
  expanded.value = new Set(expanded.value).add(row.key);
}

/**
 * Drop a column. Refused on one Games have been scored on: the scores would
 * cascade away with it, taking the Standings computed from them.
 */
function removeRow(index: number): void {
  const current = rows.value;

  if (current === null || current[index]?.is_scored === true) {
    return;
  }

  current.splice(index, 1);
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
    abbreviation: row.abbreviation,
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

/**
 * What a place is called follows the size being chosen, not the size the
 * Event was saved at: an Organiser who has just picked doubles is buying
 * teams from that moment, and a field still counting Attendees reads as the
 * limit meaning something other than it does.
 */
const placesLabel = computed(() => (Number(form.value?.attendee_size ?? 1) > 1 ? 'Max Teams' : 'Max Attendees'));

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
        <!-- Above the two fields it explains rather than under them: a
             disabled select says nothing about why it will not open, and a
             reason read after the reader has already tried is a reason late. -->
        <AppAlert
          v-if="shapeLocked"
          data-testid="format-shape-locked"
          tone="warning"
        >
          <span class="flex items-start gap-x-2">
            <TriangleAlert class="mt-px size-4 shrink-0" />
            Game system and team size are now fixed.
          </span>
        </AppAlert>

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
          label="Team size"
          :options="partySizeOptions"
          :disabled="shapeLocked"
          testid="format-attendee-size"
          :errors="errors.attendee_size"
        />

        <TextField
          v-model="form.max_attendees"
          :label="placesLabel"
          inputmode="numeric"
          hint="Leave empty for no limit."
          testid="format-max-attendees"
          :errors="errors.max_attendees"
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
          <div
            v-for="(row, index) in rows"
            :key="row.key"
            data-testid="format-score-type"
            class="flex flex-col gap-4 rounded-lg border border-card-line bg-card p-4"
          >
            <div class="flex items-start justify-between gap-3">
              <button
                type="button"
                :data-testid="`score-toggle-${index}`"
                :aria-expanded="open(index, row.key)"
                :aria-controls="`score-fields-${index}`"
                class="flex min-w-0 flex-1 items-start gap-x-2 text-left"
                @click="toggle(row.key)"
              >
                <ChevronDown
                  class="mt-0.5 size-4 shrink-0 text-muted-foreground transition-transform"
                  :class="open(index, row.key) ? '' : '-rotate-90'"
                />
                <span class="min-w-0">
                  <span class="block truncate text-sm font-medium text-foreground">
                    {{ row.name === '' ? `Column ${index + 1}` : row.name }}
                    <span
                      v-if="row.name !== ''"
                      class="text-muted-foreground-1"
                    >· {{ headingOf(row) }}</span>
                  </span>
                  <span
                    v-if="!open(index, row.key)"
                    class="block truncate text-xs text-muted-foreground-1"
                  >{{ summaryOf(row) }}</span>
                </span>
              </button>
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
                  :data-testid="`score-remove-${index}`"
                  :disabled="row.is_scored"
                  class="rounded-md border border-card-line p-1.5 text-muted-foreground hover:text-destructive disabled:opacity-40"
                  :aria-label="`Remove ${row.name}`"
                  @click="removeRow(index)"
                >
                  <Trash2 class="size-4" />
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

            <div
              v-if="open(index, row.key)"
              :id="`score-fields-${index}`"
              class="flex flex-col gap-4"
            >
              <TextField
                v-model="row.name"
                label="Name"
                :testid="`score-name-${index}`"
                :errors="rowErrors(index, 'name')"
              />

              <TextField
                v-model="row.abbreviation"
                label="Abbreviation"
                hint="Shown over the column on a game and in the standings. Left empty, the initials of the name are used."
                :testid="`score-abbreviation-${index}`"
                :errors="rowErrors(index, 'abbreviation')"
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
                label="Order"
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
                  <span>Show on game listing</span>
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
                  <span>Show in standings</span>
                  <Check
                    v-if="row.counts_for_ranking"
                    class="size-4 shrink-0"
                  />
                </button>
              </div>

              <AppAlert
                v-if="row.is_scored"
                tone="warning"
              >
                <span class="flex items-start gap-x-2">
                  <TriangleAlert class="mt-px size-4 shrink-0" />
                  Games have already been scored on this column.
                </span>
              </AppAlert>
            </div>
          </div>

          <p
            v-if="rows.length === 0"
            data-testid="format-no-score-types"
            class="text-sm text-muted-foreground-1"
          >
            No scoring set up yet.
          </p>

          <button
            type="button"
            data-testid="score-add"
            class="rounded-lg border border-dashed border-card-line px-4 py-3 text-sm text-muted-foreground hover:text-foreground"
            @click="addRow"
          >
            Add a score
          </button>

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
