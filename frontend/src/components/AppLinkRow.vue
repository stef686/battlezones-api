<script setup lang="ts">
import { ChevronRight } from 'lucide-vue-next';
import { RouterLink, type RouteLocationRaw } from 'vue-router';

/**
 * One row of a list group: what it leads to on the left, where that stands on
 * the right, and a chevron saying it opens rather than acts.
 */
defineProps<{
  to: RouteLocationRaw;
  label: string;
  /** How this section stands right now — a name, a count, "Not chosen". */
  value?: string | null;
  /** Whether that state is something the Player still has to deal with. */
  outstanding?: boolean;
  testid?: string;
}>();
</script>

<template>
  <li>
    <RouterLink
      :to="to"
      :data-testid="testid"
      class="flex items-center gap-3 px-5 py-4 hover:bg-muted-hover focus:bg-muted-hover focus:outline-hidden"
    >
      <span class="min-w-0 flex-1 text-sm font-medium text-foreground">{{ label }}</span>
      <span
        v-if="value"
        data-testid="row-value"
        class="min-w-0 truncate text-sm"
        :class="outstanding ? 'text-muted-foreground' : 'text-muted-foreground-1'"
      >{{ value }}</span>
      <ChevronRight class="size-4 shrink-0 text-muted-foreground" />
    </RouterLink>
  </li>
</template>
