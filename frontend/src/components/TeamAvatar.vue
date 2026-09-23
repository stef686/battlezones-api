<script setup lang="ts">
import { computed } from 'vue';

/**
 * A team's badge, or a placeholder standing in for one.
 *
 * Most teams never upload an Avatar, so the placeholder is the common case
 * rather than an error state: it keeps every row the same shape and carries
 * the team's initials, which is more use in a list of forty than an empty
 * circle would be. The image is decorative — the name it sits beside is the
 * accessible one — so it is hidden from screen readers rather than repeating
 * the row aloud.
 */
const props = withDefaults(defineProps<{
  name: string;
  src?: string | null;
  size?: 'xs' | 'sm' | 'lg';
}>(), { src: null, size: 'sm' });

const SIZES = {
  xs: 'size-6 text-2xs',
  sm: 'size-8 text-xs',
  lg: 'size-20 text-xl',
} as const;

const classes = computed(() => [SIZES[props.size], 'shrink-0 rounded-full object-cover']);

/** Words that carry no initial worth drawing: "Sons of Terra" is ST, not SO. */
const JOINING_WORDS = ['of', 'the', 'and', 'a', 'an', '&'];

/** Up to two initials: more than that is unreadable at 24px. */
const initials = computed(() => {
  const words = props.name
    .split(/\s+/)
    .filter((word) => word.length > 0 && !JOINING_WORDS.includes(word.toLowerCase()));

  return words
    .slice(0, 2)
    .map((word) => word[0]?.toUpperCase() ?? '')
    .join('');
});
</script>

<template>
  <img
    v-if="src"
    :src="src"
    alt=""
    aria-hidden="true"
    data-testid="team-avatar"
    :class="classes"
  >
  <span
    v-else
    aria-hidden="true"
    data-testid="team-avatar-placeholder"
    :class="[...classes, 'flex items-center justify-center border border-card-line bg-surface font-semibold text-muted-foreground-1']"
  >{{ initials }}</span>
</template>
