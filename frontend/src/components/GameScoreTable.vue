<script setup lang="ts">
/**
 * One Game's scoreline: the teams read down, the Score Types read across.
 *
 * A real table, because that is what this is. Letting the browser size the
 * columns keeps the headings centred over their numbers however wide the
 * label or the score turns out to be, which hand-set widths only manage until
 * the first three-digit score.
 *
 * Drawn identically on the Round's cards and at the top of a Game, so a
 * Player who taps a card is looking at the same rows they just tapped.
 */
import { CircleCheck } from 'lucide-vue-next';

import { columnLabel, type ScoreColumn, type ScoredAttendee } from '@/api/rounds';
import { formatScore } from '@/lib/scores';

defineProps<{
  attendees: ScoredAttendee[];
  /**
   * The columns the Game is played on, sent by the API whether or not any
   * score has landed. A Game nobody has played yet shows what it is waiting
   * for rather than collapsing to nothing.
   */
  columns: ScoreColumn[];
}>();

/** A team's score in one column, which is zero until somebody says otherwise. */
function scoreOf(attendee: ScoredAttendee, column: string): string {
  return formatScore(attendee.scores[column] ?? 0);
}
</script>

<template>
  <table class="w-full">
    <thead>
      <tr
        data-testid="pairing-columns"
        class="bg-background-1 text-xs uppercase tracking-widest text-muted-foreground"
      >
        <!-- The names below need no heading, and the empty cell takes the
             width the score columns do not. -->
        <th
          scope="col"
          class="w-full px-3 py-2 text-start font-medium"
        >
          <span class="sr-only">Team</span>
        </th>
        <th
          v-for="column in columns"
          :key="column.slug"
          :data-testid="`column-${column.slug}`"
          scope="col"
          class="px-3 py-2 text-center font-bold whitespace-nowrap"
        >
          {{ columnLabel(column) }}
          <span class="sr-only">{{ column.name }}</span>
        </th>
      </tr>
    </thead>

    <tbody class="divide-y divide-card-divider border-t border-card-divider">
      <tr
        v-for="attendee in attendees"
        :key="attendee.id"
        :data-testid="`pairing-team-${attendee.id}`"
      >
        <!-- The winner is both weighted and ticked, rather than either alone:
             weight alone is a difference a reader has to notice by comparing
             the two rows, and a tick alone carries no meaning at a glance
             across a hall of cards. -->
        <th
          scope="row"
          class="max-w-0 px-3 py-2 text-start text-xs text-foreground"
          :class="attendee.is_winner ? 'font-semibold' : 'font-normal'"
        >
          <span class="flex min-w-0 items-center gap-1.5">
            <span class="truncate">{{ attendee.name }}</span>

            <span
              v-if="attendee.is_winner"
              :data-testid="`winner-${attendee.id}`"
              class="inline-flex shrink-0 items-center text-success"
            >
              <CircleCheck class="size-4 shrink-0" />
              <span class="sr-only">Won</span>
            </span>
          </span>
        </th>
        <td
          v-for="column in columns"
          :key="column.slug"
          :data-testid="`score-${column.slug}`"
          class="px-3 py-2 text-center text-xs font-medium tabular-nums whitespace-nowrap text-foreground"
        >
          {{ scoreOf(attendee, column.slug) }}
        </td>
      </tr>
    </tbody>
  </table>
</template>
