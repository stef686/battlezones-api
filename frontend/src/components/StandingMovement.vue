<script setup lang="ts">
/**
 * How far a team has moved since the Round before the one being played.
 *
 * A standings table says who is ahead; it does not say who is climbing, which
 * is the thing a Player reads it for between Rounds. The arrow carries that
 * without spending a column on it.
 *
 * Direction is colour and shape together rather than colour alone — an arrow
 * that only differs by being red says nothing to a reader who cannot tell it
 * from the green one — and the places gained go to a screen reader, which has
 * no arrow to read.
 *
 * Every row carries a mark, including the first Round where nobody has moved
 * yet: a column that appears a Round in shifts the numbers beside it, and a
 * dash on every row says "nothing has happened here" where a blank row only
 * looks like a mark that failed to load.
 */
import { CircleArrowDown, CircleArrowUp, CircleMinus } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
  /**
   * Places gained: positive for a climb, negative for a drop, zero for
   * holding. Null where there is nothing to compare against yet, which draws
   * the same dash as holding — the table reads as "nobody has moved", which
   * during a first Round is exactly what has happened.
   */
  movement: number | null;
}>();

const places = computed(() => Math.abs(props.movement ?? 0));

const icon = computed(() => {
  if (props.movement === null || props.movement === 0) {
    return CircleMinus;
  }

  return props.movement > 0 ? CircleArrowUp : CircleArrowDown;
});

const tone = computed(() => {
  if (props.movement === null || props.movement === 0) {
    return 'text-muted-foreground';
  }

  return props.movement > 0 ? 'text-success' : 'text-destructive';
});

const description = computed(() => {
  if (props.movement === null || props.movement === 0) {
    return 'No change';
  }

  const direction = props.movement > 0 ? 'Up' : 'Down';

  return `${direction} ${places.value} ${places.value === 1 ? 'place' : 'places'}`;
});
</script>

<template>
  <!-- No testid of its own: the table names each mark by the team it belongs
       to, and a fallthrough attribute would overwrite one set here anyway. -->
  <span
    class="inline-flex shrink-0 items-center"
    :class="tone"
  >
    <component
      :is="icon"
      class="size-3.5 shrink-0"
    />
    <span class="sr-only">{{ description }}</span>
  </span>
</template>
