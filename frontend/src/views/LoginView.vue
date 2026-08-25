<script setup lang="ts">
import { computed, ref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';

import { DEVICE_NAME, useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import TextField from '@/components/TextField.vue';
import { LANDING_EVENT_SLUG } from '@/router';
import { useSessionStore } from '@/stores/session';

const client = useApiClient();
const session = useSessionStore();
const router = useRouter();
const route = useRoute();

const email = ref('');
const password = ref('');
const submitting = ref(false);
const error = ref<ApiError | null>(null);

/** Set when a password reset landed but signing in with it did not. */
const afterReset = computed(() => route.query.reset === '1');

function fieldErrors(field: string): string[] {
  return error.value?.fields[field] ?? [];
}

async function submit(): Promise<void> {
  submitting.value = true;
  error.value = null;

  try {
    await client.login(email.value, password.value, DEVICE_NAME);

    // Logging in with a password is the end of any invited session that came
    // before it, so nothing is left to confine this one to one Event.
    session.forgetInvite();
    await session.load(client);

    const redirect = route.query.redirect;

    await router.replace(typeof redirect === 'string' && redirect !== '' ? redirect : { name: 'my-game', params: { eventSlug: LANDING_EVENT_SLUG } });
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

    <p
      v-if="afterReset"
      data-testid="password-was-reset"
      class="rounded-2xl bg-surface-raised p-5 text-success"
    >
      Your password has been reset. Log in with it to carry on.
    </p>

    <form
      class="flex flex-col gap-4"
      novalidate
      @submit.prevent="submit"
    >
      <TextField
        v-model="email"
        label="Email"
        type="email"
        inputmode="email"
        autocomplete="email"
        testid="email"
        :errors="fieldErrors('email')"
      />

      <TextField
        v-model="password"
        label="Password"
        type="password"
        autocomplete="current-password"
        testid="password"
        :errors="fieldErrors('password')"
      />

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

    <RouterLink
      :to="{ name: 'forgot-password' }"
      data-testid="forgot-password-link"
      class="text-center text-ink-muted underline underline-offset-4"
    >
      I have forgotten my password
    </RouterLink>
  </main>
</template>
