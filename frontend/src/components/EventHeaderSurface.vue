<script setup lang="ts">
/**
 * What an Event's header looks like, given what it shows: the Banner (or the
 * flat surface), the scrims, and the Game System, name and dates over them.
 *
 * Drawn by `EventHeader` from the Event it reads, and by the create wizard's
 * Look preview from an Event not made yet, so the preview is the header rather
 * than a picture of it. `insetTop` is the header's alone: it runs under the
 * status bar and only the type clears it, which a preview inside a page has
 * no reason to do.
 *
 * Overlay text sits on scrims that are there whether or not a Banner is, so
 * it has one colour rule in every state — a pale Banner cannot make it
 * unreadable, and muting a dark one is the accepted cost.
 */
withDefaults(defineProps<{
  name: string;
  gameSystem?: string | null;
  dates?: string;
  banner?: { large: string; small: string } | null;
  tag?: 'header' | 'div';
  insetTop?: boolean;
}>(), {
  gameSystem: null,
  dates: '',
  banner: null,
  tag: 'div',
  insetTop: false,
});
</script>

<template>
  <component
    :is="tag"
    class="relative isolate w-full bg-navbar"
    :class="insetTop ? 'pt-[env(safe-area-inset-top)]' : ''"
  >
    <!-- Decorative: the Event's name is already in the heading below it, so
         an alt text here would read the same thing twice. -->
    <img
      v-if="banner"
      :src="banner.large"
      :srcset="`${banner.small} 800w, ${banner.large} 1600w`"
      sizes="100vw"
      alt=""
      data-testid="event-header-banner"
      class="absolute inset-0 -z-10 size-full object-cover"
    >

    <!-- Light status-bar text reads over the top of the header whatever is
         behind it, so the scrim is here before there is ever an image. -->
    <div
      aria-hidden="true"
      class="pointer-events-none absolute inset-x-0 top-0 h-20 event-header-scrim-top"
    />

    <div class="relative flex h-40 flex-col justify-end">
      <div
        aria-hidden="true"
        class="pointer-events-none absolute inset-x-0 bottom-0 h-28 event-header-scrim-bottom"
      />

      <div class="relative mx-auto flex w-full max-w-md flex-col px-5 pb-4 leading-tight">
        <p
          v-if="gameSystem"
          data-testid="event-header-game-system"
          class="text-xs font-medium uppercase tracking-wide text-event-header-foreground-muted"
        >
          {{ gameSystem }}
        </p>

        <h1
          v-if="tag === 'header'"
          data-testid="event-header-name"
          class="text-2xl font-semibold text-event-header-foreground"
        >
          {{ name }}
        </h1>
        <!-- A preview sits inside a page that already has its heading. -->
        <p
          v-else
          data-testid="event-header-name"
          class="text-2xl font-semibold text-event-header-foreground"
        >
          {{ name }}
        </p>

        <p
          v-if="dates"
          data-testid="event-header-dates"
          class="text-sm text-event-header-foreground-muted"
        >
          {{ dates }}
        </p>
      </div>
    </div>
  </component>
</template>
