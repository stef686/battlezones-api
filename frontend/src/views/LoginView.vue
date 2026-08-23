<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router';
import { ref } from 'vue';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { useSessionStore } from '@/stores/session';

const client = useApiClient();
const session = useSessionStore();
const router = useRouter();
const route = useRoute();

const email = ref('');
const password = ref('');
const submitting = ref(false);
const error = ref<ApiError | null>(null);

function fieldErrors(field: string): string[] {
  return error.value?.fields[field] ?? [];
}

async function submit(): Promise<void> {
  submitting.value = true;
  error.value = null;

  try {
    await client.login(email.value, password.value, 'Battlezones Web');
    await session.load(client);

    const redirect = route.query.redirect;

    await router.replace(typeof redirect === 'string' && redirect !== '' ? redirect : { name: 'my-game', params: { eventSlug: 'end-to-end-open' } });
  } catch (caught) {
    error.value = caught instanceof ApiError ? caught : null;
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <main class="mx-auto flex min-h-screen w-full max-w-sm flex-col justify-center gap-8 p-6">
    <header>
      <h1 class="text-2xl font-semibold tracking-tight text-ink">
        Battlezones
      </h1>
      <p class="mt-1 text-sm text-ink-muted">
        Log in to see your game.
      </p>
    </header>

    <form
      class="flex flex-col gap-4"
      novalidate
      @submit.prevent="submit"
    >
      <label class="flex flex-col gap-1.5">
        <span class="text-sm text-ink-muted">Email</span>
        <input
          v-model="email"
          type="email"
          name="email"
          autocomplete="email"
          data-testid="email"
          class="rounded-lg border border-border bg-surface-sunken px-3 py-2.5 text-ink outline-none focus:border-accent"
        >
        <span
          v-for="message in fieldErrors('email')"
          :key="message"
          class="text-sm text-danger"
        >{{ message }}</span>
      </label>

      <label class="flex flex-col gap-1.5">
        <span class="text-sm text-ink-muted">Password</span>
        <input
          v-model="password"
          type="password"
          name="password"
          autocomplete="current-password"
          data-testid="password"
          class="rounded-lg border border-border bg-surface-sunken px-3 py-2.5 text-ink outline-none focus:border-accent"
        >
        <span
          v-for="message in fieldErrors('password')"
          :key="message"
          class="text-sm text-danger"
        >{{ message }}</span>
      </label>

      <p
        v-if="error && error.kind !== 'validation'"
        data-testid="login-error"
        class="text-sm text-danger"
      >
        {{ error.message }}
      </p>

      <button
        type="submit"
        data-testid="submit-login"
        :disabled="submitting"
        class="mt-2 rounded-lg bg-accent px-4 py-3 font-semibold text-accent-ink disabled:opacity-60"
      >
        {{ submitting ? 'Logging in…' : 'Log in' }}
      </button>
    </form>
  </main>
</template>
