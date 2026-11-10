<script setup lang="ts">
/**
 * The ordered columns an Event is scored on, as its Organiser edits them.
 *
 * Edits a list its parent owns and never writes to the API: the set is sent
 * whole, by the parent, because position is both the display order and — among
 * the rows that count — the ranking order, and half a reorder is no order.
 *
 * Errors are keyed by row and field (`0.name`), never by where the list sits
 * in a request body, so the parent maps the API's paths onto these.
 */
import { Check, ChevronDown, ChevronUp, Trash2, TriangleAlert } from 'lucide-vue-next';
import { ref } from 'vue';

import { columnLabel } from '@/api/rounds';
import AppAlert from '@/components/AppAlert.vue';
import SelectField from '@/components/SelectField.vue';
import TextField from '@/components/TextField.vue';
import { blankScoreTypeRow, type FieldErrors, type ScoreTypeRow } from '@/lib/event-fields';

const props = withDefaults(defineProps<{ errors?: FieldErrors }>(), { errors: () => ({}) });

const model = defineModel<ScoreTypeRow[]>({ required: true });

const sourceOptions = [
  { value: 'entered', label: 'Game score' },
  { value: 'derived', label: 'Calculated from the result' },
];

const directionOptions = [
  { value: 'desc', label: 'Higher to lower' },
  { value: 'asc', label: 'Lower to higher' },
];

function updateRow(index: number, changes: Partial<ScoreTypeRow>): void {
  model.value = model.value.map((row, position) => (position === index ? { ...row, ...changes } : row));
}

/** Move a row up or down; its new position is its display and ranking order. */
function move(index: number, by: number): void {
  const target = index + by;

  if (target < 0 || target >= model.value.length) {
    return;
  }

  const reordered = [...model.value];
  const [moved] = reordered.splice(index, 1);

  if (moved !== undefined) {
    reordered.splice(target, 0, moved);
    model.value = reordered;
  }
}

/**
 * Only one row may lead a Game listing, so claiming it takes it off whoever
 * held it — the API refuses two, and being told off for a toggle you can see
 * is worse than the editor just doing the obvious thing.
 */
function claimLead(index: number): void {
  const claiming = model.value[index]?.is_primary !== true;

  model.value = model.value.map((row, position) => ({ ...row, is_primary: claiming && position === index }));
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
  const next = new Set(expanded.value);

  if (next.has(key)) {
    next.delete(key);
  } else {
    next.add(key);
  }

  expanded.value = next;
}

/**
 * A folded column with something to answer for is unfolded by the editor: a
 * rejected field that cannot be seen is a form that will not save and will not
 * say why.
 */
function open(index: number, key: string): boolean {
  return expanded.value.has(key) || rowRejected(index);
}

function rowRejected(index: number): boolean {
  return Object.keys(props.errors).some((field) => field.startsWith(`${index}.`));
}

/** The messages keyed to one row's field, if any. */
function rowErrors(index: number, field: keyof ScoreTypeRow): string[] {
  return props.errors[`${index}.${field}`] ?? [];
}

/** What a folded column says about itself, under its name. */
function summaryOf(row: ScoreTypeRow): string {
  const source = sourceOptions.find((option) => option.value === (row.is_derived ? 'derived' : 'entered'));
  const direction = directionOptions.find((option) => option.value === row.sort_direction);

  return `${source?.label} · ${direction?.label.toLowerCase()}`;
}

/**
 * The heading a folded column says it will be shown under, which is the
 * Organiser's own where they wrote one and the API's answer where they did
 * not — worked out here so the card reads the same before a save as after it.
 */
function headingOf(row: ScoreTypeRow): string {
  return columnLabel({ name: row.name, abbreviation: row.abbreviation });
}

function addRow(): void {
  const row = blankScoreTypeRow();

  model.value = [...model.value, row];
  expanded.value = new Set(expanded.value).add(row.key);
}

/**
 * Drop a column. Refused on one Games have been scored on: the scores would
 * cascade away with it, taking the Standings computed from them.
 */
function removeRow(index: number): void {
  if (model.value[index]?.is_scored === true) {
    return;
  }

  model.value = model.value.filter((_, position) => position !== index);
}
</script>

<template>
  <div class="flex flex-col gap-4">
    <div
      v-for="(row, index) in model"
      :key="row.key"
      data-testid="score-type"
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
            :disabled="index === model.length - 1"
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
          :model-value="row.name"
          label="Name"
          :testid="`score-name-${index}`"
          :errors="rowErrors(index, 'name')"
          @update:model-value="updateRow(index, { name: $event })"
        />

        <TextField
          :model-value="row.abbreviation"
          label="Abbreviation"
          :testid="`score-abbreviation-${index}`"
          :errors="rowErrors(index, 'abbreviation')"
          @update:model-value="updateRow(index, { abbreviation: $event })"
        />

        <SelectField
          :model-value="row.is_derived ? 'derived' : 'entered'"
          label="How it is scored"
          :options="sourceOptions"
          :testid="`score-source-${index}`"
          :errors="rowErrors(index, 'is_derived')"
          @update:model-value="updateRow(index, { is_derived: $event === 'derived' })"
        />

        <div
          v-if="row.is_derived"
          class="grid grid-cols-3 gap-3"
        >
          <TextField
            :model-value="row.win_points"
            label="Win"
            inputmode="numeric"
            :testid="`score-win-${index}`"
            :errors="rowErrors(index, 'win_points')"
            @update:model-value="updateRow(index, { win_points: $event })"
          />
          <TextField
            :model-value="row.draw_points"
            label="Draw"
            inputmode="numeric"
            :testid="`score-draw-${index}`"
            :errors="rowErrors(index, 'draw_points')"
            @update:model-value="updateRow(index, { draw_points: $event })"
          />
          <TextField
            :model-value="row.loss_points"
            label="Loss"
            inputmode="numeric"
            :testid="`score-loss-${index}`"
            :errors="rowErrors(index, 'loss_points')"
            @update:model-value="updateRow(index, { loss_points: $event })"
          />
        </div>

        <SelectField
          :model-value="row.sort_direction"
          label="Order"
          :options="directionOptions"
          :testid="`score-direction-${index}`"
          :errors="rowErrors(index, 'sort_direction')"
          @update:model-value="updateRow(index, { sort_direction: $event === 'asc' ? 'asc' : 'desc' })"
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
            @click="updateRow(index, { counts_for_ranking: !row.counts_for_ranking })"
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
      v-if="model.length === 0"
      data-testid="no-score-types"
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
  </div>
</template>
