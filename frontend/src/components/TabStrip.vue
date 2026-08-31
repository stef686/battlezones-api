<script setup lang="ts" generic="T extends { id: number; name: string }">
import { computed } from 'vue';

/**
 * One row of tabs and the panel beneath it.
 *
 * Two screens tab the same way — a Game by the sides of the table, a team by
 * the Players in it — and a tablist is not markup to write twice: it owes a
 * reader arrow keys, a single tab stop, and the roles that tie each tab to
 * the panel it opens. Written once, that is a thing that either works
 * everywhere or is broken everywhere.
 */
const props = defineProps<{
  /** What the tabs stand for. The `name` is the tab's label. */
  items: T[];
  /** Names the tablist for a screen reader, which sees no heading above it. */
  label: string;
  /** Prefixes every id and testid, so a screen keeps its own vocabulary. */
  idPrefix: string;
}>();

const selected = defineModel<number>({ required: true });

const open = computed<T | null>(() => props.items[selected.value] ?? null);

/**
 * Move between tabs on the arrow keys, which is what a tablist owes a reader
 * who is not using a pointer. The ends wrap, so neither arrow ever dead-ends.
 */
function onKeydown(event: KeyboardEvent): void {
  const last = props.items.length - 1;

  const target = {
    ArrowRight: selected.value === last ? 0 : selected.value + 1,
    ArrowLeft: selected.value === 0 ? last : selected.value - 1,
    Home: 0,
    End: last,
  }[event.key];

  if (target === undefined) {
    return;
  }

  event.preventDefault();
  selected.value = target;
  document.getElementById(tabId(props.items[target] as T))?.focus();
}

function tabId(item: { id: number }): string {
  return `${props.idPrefix}-tab-${item.id}`;
}

function panelId(item: { id: number }): string {
  return `${props.idPrefix}-panel-${item.id}`;
}
</script>

<template>
  <div class="flex flex-col gap-4">
    <!-- The tabs split the width evenly rather than each taking what its name
         happens to need: tabs of the same size read as parts of one whole,
         and a long name would otherwise push its neighbour into a corner of
         the screen. A name too long for its share is truncated, with the
         tooltip and the panel below giving it back in full — the
         alternatives are wrapping to a second row, which moves the panel
         every time the reader changes tab, or scrolling, which hides tabs. -->
    <div
      role="tablist"
      :aria-label="label"
      :data-testid="`${idPrefix}-tabs`"
      class="flex border-b border-card-line"
      @keydown="onKeydown"
    >
      <!-- A tablist is a single tab stop: only the open tab is reachable with
           Tab, and the arrows move between them. -->
      <button
        v-for="(item, index) in items"
        :id="tabId(item)"
        :key="item.id"
        type="button"
        role="tab"
        :data-testid="tabId(item)"
        :aria-selected="index === selected"
        :aria-controls="panelId(item)"
        :tabindex="index === selected ? 0 : -1"
        :title="item.name"
        class="min-w-0 flex-1 truncate border-b-2 px-3 py-2 text-sm font-medium focus:outline-hidden"
        :class="index === selected
          ? 'border-primary text-foreground'
          : 'border-transparent text-muted-foreground-1 hover:text-foreground focus:text-foreground'"
        @click="selected = index"
      >
        {{ item.name }}
      </button>
    </div>

    <div
      v-if="open"
      :id="panelId(open)"
      :key="open.id"
      role="tabpanel"
      :aria-labelledby="tabId(open)"
      :data-testid="panelId(open)"
      tabindex="0"
      class="flex flex-col gap-3 focus:outline-hidden"
    >
      <slot :item="open" />
    </div>
  </div>
</template>
