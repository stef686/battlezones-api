<script setup lang="ts">
import { ChevronLeft } from 'lucide-vue-next';
import { RouterLink, type RouteLocationRaw } from 'vue-router';

/** The way back from a screen the Event nav does not itself land on. */
defineProps<{ to: RouteLocationRaw; testid?: string }>();
</script>

<template>
  <RouterLink
    :to="to"
    :data-testid="testid"
    class="inline-flex items-center gap-x-1 self-start text-sm font-medium text-muted-foreground-1 hover:text-foreground focus:text-foreground focus:outline-hidden"
  >
    <ChevronLeft class="size-4 shrink-0" />
    <slot />
  </RouterLink>
</template>
