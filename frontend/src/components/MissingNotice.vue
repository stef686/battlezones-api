<script setup lang="ts">
/**
 * What a reader sees when a thing is not there.
 *
 * One component, one wording, deliberately incurious: an Event that is not
 * publicly visible and one that never existed answer identically, because
 * telling them apart is the leak. Nothing here may say "private", "hidden",
 * "not published" or "no permission" — that is the whole point of it being
 * in one place rather than written out on each screen.
 */
withDefaults(defineProps<{ thing?: string }>(), { thing: 'page' });
</script>

<template>
  <section
    data-testid="missing"
    class="flex flex-col gap-3 rounded-2xl bg-surface-raised p-6"
  >
    <h1 class="text-xl font-semibold tracking-tight text-ink">
      Not found
    </h1>
    <p class="text-ink-muted">
      We could not find that {{ thing }}. Check the link, or ask whoever sent it to you.
    </p>
  </section>
</template>
