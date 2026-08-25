<script setup lang="ts">
/**
 * The card every screen reached without a session is drawn in.
 *
 * Log in, claim, reset, and the two token screens are the same shape: a
 * centred card, a heading, a form, and one way onwards underneath. Written
 * once so a change of shape is a change here, rather than five screens that
 * drift apart.
 */
defineProps<{ title: string; subtitle?: string }>();
</script>

<template>
  <main class="mx-auto flex min-h-screen w-full max-w-sm flex-col justify-center gap-6 p-6">
    <div class="rounded-xl border border-card-line bg-card shadow-2xs">
      <div class="p-6 sm:p-7">
        <header class="text-center">
          <h1 class="block text-2xl font-bold tracking-tight text-foreground">
            {{ title }}
          </h1>
          <p
            v-if="subtitle"
            class="mt-2 text-sm text-muted-foreground-1"
          >
            {{ subtitle }}
          </p>
        </header>

        <div class="mt-6">
          <slot />
        </div>
      </div>

      <div
        v-if="$slots.footer"
        class="border-t border-card-divider px-6 py-4 text-center sm:px-7"
      >
        <slot name="footer" />
      </div>
    </div>
  </main>
</template>
