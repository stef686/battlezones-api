<script setup lang="ts">
/**
 * One Game's scoreline: the teams read down, the Score Types read across.
 *
 * A real table, because that is what this is.
 *
 * It is drawn two ways. On a Game it is a `detail` table: every column the
 * Event scores on, named above its numbers, the browser sizing each so a
 * heading stays centred over a three-digit score. In a Round's listing it is
 * a `listing` row: one column, no visible heading, and a fixed-width score
 * pinned right so a run of Games reads as two straight edges rather than a
 * ragged one — the names take everything that is left.
 *
 * Neither carries gutters of its own. The screen's padding is what the rows
 * line up with, so the names start where the rest of the page starts; the
 * space between the detail table's columns is interior and stops at the last
 * one, which sits flush against the right edge.
 */
import { CircleCheck } from 'lucide-vue-next';
import { computed } from 'vue';

import { columnLabel, type ScoreColumn, type ScoredAttendee } from '@/api/rounds';
import TeamAvatar from '@/components/TeamAvatar.vue';
import { formatScore } from '@/lib/scores';

const props = withDefaults(defineProps<{
  attendees: ScoredAttendee[];
  /**
   * The columns the Game is played on, sent by the API whether or not any
   * score has landed. A Game nobody has played yet shows what it is waiting
   * for rather than collapsing to nothing.
   */
  columns: ScoreColumn[];
  /**
   * Where the table is being drawn. A `listing` hides its headings — they
   * stay in the markup, because a number with no name is nothing to a screen
   * reader, but the same column repeats down every Game in the Round and the
   * initials only compete with the scores.
   */
  variant?: 'listing' | 'detail';
}>(), { variant: 'detail' });

const isListing = computed(() => props.variant === 'listing');

/**
 * A listed score is one fixed column on the right; a detail one is as wide as
 * its heading needs, spaced from its neighbours and flush at the last.
 */
const scoreCell = computed(() => isListing.value ? 'w-16 text-end' : 'px-3 text-center last:pe-0');

/**
 * A team's score in one column, or a dash where there is none.
 *
 * A Game nobody has played carries no scores at all, and drawing those as
 * zeroes reads as a played nil-all rather than as a table still out there. A
 * zero that was actually entered still reads as a zero.
 */
function scoreOf(attendee: ScoredAttendee, column: string): string {
  const score = attendee.scores[column];

  return score === undefined || score === null ? '—' : formatScore(score);
}
</script>

<template>
  <table class="w-full">
    <thead>
      <tr
        data-testid="pairing-columns"
        class="text-xs uppercase tracking-widest text-muted-foreground"
        :class="isListing ? 'sr-only' : ''"
      >
        <!-- The names below need no heading, and the empty cell takes the
             width the score columns do not. -->
        <th
          scope="col"
          class="w-full py-2 text-start font-medium"
        >
          <span class="sr-only">Team</span>
        </th>
        <th
          v-for="column in columns"
          :key="column.slug"
          :data-testid="`column-${column.slug}`"
          scope="col"
          class="py-2 font-bold whitespace-nowrap"
          :class="scoreCell"
        >
          {{ columnLabel(column) }}
          <span class="sr-only">{{ column.name }}</span>
        </th>
      </tr>
    </thead>

    <tbody>
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
          class="max-w-0 py-2 text-start text-xs text-foreground"
          :class="attendee.is_winner ? 'font-semibold' : 'font-normal'"
        >
          <span class="flex min-w-0 items-center gap-1.5">
            <TeamAvatar
              :name="attendee.name"
              :src="attendee.avatar"
              size="xs"
            />
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
          :data-testid="`score-${attendee.id}-${column.slug}`"
          class="py-2 text-xs font-medium tabular-nums whitespace-nowrap text-foreground"
          :class="scoreCell"
        >
          {{ scoreOf(attendee, column.slug) }}
        </td>
      </tr>
    </tbody>
  </table>
</template>
