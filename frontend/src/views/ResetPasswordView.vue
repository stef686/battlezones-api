<script setup lang="ts">
import { computed, ref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';

import { DEVICE_NAME, useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import TextField from '@/components/TextField.vue';
import { useSessionStore } from '@/stores/session';

const client = useApiClient();
const session = useSessionStore();
const route = useRoute();
const router = useRouter();

/** The emailed link carries both. Without them there is nothing to reset. */
const token = computed(() => queryValue('token'));
const email = computed(() => queryValue('email'));

const password = ref('');
const passwordConfirmation = ref('');
const submitting = ref(false);
const error = ref<ApiError | null>(null);

function queryValue(key: string): string | null {
  const value = route.query[key];

  return typeof value === 'string' && value !== '' ? value : null;
}

function fieldErrors(field: string): string[] {
  return error.value?.fields[field] ?? [];
}

/**
 * Reset, then sign in with what was just set.
 *
 * The reset endpoint returns no session, and asking someone on a phone in a
 * hall to retype the password they typed ten seconds ago is how a recovery
 * flow loses the person it exists for. If the sign-in fails, the reset still
 * happened, so they are sent to log in rather than told the reset failed.
 */
async function submit(): Promise<void> {
  if (token.value === null || email.value === null) {
    return;
  }

  submitting.value = true;
  error.value = null;

  try {
    await client.post('/api/auth/reset-password', {
      token: token.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    });
  } catch (caught) {
    error.value = caught instanceof ApiError ? caught : null;
    submitting.value = false;

    return;
  }

  try {
    await client.login(email.value, password.value, DEVICE_NAME);
    session.forgetInvite();
    await session.load(client);

    await router.replace('/');
  } catch {
    await router.replace({ name: 'login', query: { reset: '1' } });
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <main class="mx-auto flex min-h-screen w-full max-w-sm flex-col justify-center gap-8 p-6">
    <template v-if="token === null || email === null">
      <header class="flex flex-col gap-2">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">
          This link is incomplete
        </h1>
        <p
          data-testid="reset-link-broken"
          class="text-ink-muted"
        >
          Open the link from the email again, in full. Some mail apps cut long links short.
        </p>
      </header>

      <RouterLink
        :to="{ name: 'forgot-password' }"
        data-testid="request-new-reset"
        class="rounded-lg bg-accent px-4 py-3 text-center font-semibold text-accent-ink"
      >
        Send a new link
      </RouterLink>
    </template>

    <template v-else>
      <header class="flex flex-col gap-2">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">
          Set a new password
        </h1>
        <p
          data-testid="reset-email"
          class="text-sm text-ink-muted"
        >
          For {{ email }}.
        </p>
      </header>

      <form
        class="flex flex-col gap-4"
        novalidate
        @submit.prevent="submit"
      >
        <TextField
          v-model="password"
          label="New password"
          type="password"
          autocomplete="new-password"
          testid="reset-password"
          hint="At least 8 characters."
          :errors="fieldErrors('password')"
        />

        <TextField
          v-model="passwordConfirmation"
          label="Confirm password"
          type="password"
          autocomplete="new-password"
          testid="reset-password-confirmation"
          :errors="fieldErrors('password_confirmation')"
        />

        <!-- The API reports a spent or wrong token against the address. -->
        <p
          v-for="message in fieldErrors('email')"
          :key="message"
          data-testid="reset-token-error"
          class="text-sm text-danger"
        >
          {{ message }}
        </p>

        <p
          v-if="error && error.kind !== 'validation'"
          data-testid="reset-error"
          class="text-sm text-danger"
        >
          {{ error.message }}
        </p>

        <button
          type="submit"
          data-testid="submit-reset"
          :disabled="submitting"
          class="mt-2 rounded-lg bg-accent px-4 py-3 font-semibold text-accent-ink disabled:opacity-60"
        >
          {{ submitting ? 'Saving…' : 'Save password' }}
        </button>
      </form>
    </template>
  </main>
</template>
