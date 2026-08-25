<script setup lang="ts">
/**
 * The one button.
 *
 * Preline's button classes are a dozen utilities long and were being retyped
 * on every screen, which is how a codebase ends up with four slightly
 * different primary buttons. The variants here are the only ones the app has.
 *
 * A `to` turns it into a link, because "go there" and "do this" look identical
 * to a reader and should not look different to whoever is writing the screen.
 * The element still differs — a RouterLink is a real anchor, so it is
 * middle-clickable and reachable the way a link should be.
 */
import { computed } from 'vue';
import { RouterLink, type RouteLocationRaw } from 'vue-router';

const props = withDefaults(defineProps<{
  variant?: 'primary' | 'secondary' | 'ghost' | 'danger';
  size?: 'sm' | 'md';
  /** Fills its container, which is what a form's submit almost always wants. */
  block?: boolean;
  disabled?: boolean;
  type?: 'button' | 'submit';
  to?: RouteLocationRaw;
}>(), {
  variant: 'primary',
  size: 'md',
  block: false,
  disabled: false,
  type: 'button',
  to: undefined,
});

const VARIANTS = {
  primary: 'border-transparent bg-primary text-primary-foreground hover:bg-primary-hover focus:bg-primary-focus',
  secondary: 'border-border bg-card text-foreground hover:bg-muted-hover focus:bg-muted-hover',
  ghost: 'border-transparent text-muted-foreground-1 hover:bg-muted-hover hover:text-foreground focus:bg-muted-hover',
  danger: 'border-transparent bg-destructive text-destructive-foreground hover:bg-destructive-hover focus:bg-destructive-focus',
} as const;

const SIZES = {
  sm: 'px-3 py-2 text-sm',
  md: 'px-4 py-3 text-sm',
} as const;

const classes = computed(() => [
  'inline-flex items-center justify-center gap-x-2 rounded-lg border font-medium focus:outline-hidden disabled:pointer-events-none disabled:opacity-50',
  VARIANTS[props.variant],
  SIZES[props.size],
  props.block ? 'w-full' : '',
]);
</script>

<template>
  <RouterLink
    v-if="to"
    :to="to"
    :class="classes"
  >
    <slot />
  </RouterLink>
  <button
    v-else
    :type="type"
    :disabled="disabled"
    :class="classes"
  >
    <slot />
  </button>
</template>
