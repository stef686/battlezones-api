<script setup lang="ts">
import { ref } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import TextField from '@/components/TextField.vue';

const client = useApiClient();

const email = ref('');
const submitting = ref(false);
const sent = ref<string | null>(null);
const error = ref<ApiError | null>(null);

function fieldErrors(field: string): string[] {
  return error.value?.fields[field] ?? [];
}

/**
 * The API answers the same way whether or not the address is on an account,
 * so this screen does too: anything else would let a stranger test addresses.
 */
async function submit(): Promise<void> {
  submitting.value = true;
  sent.value = null;
  error.value = null;

  try {
    const response = await client.post<{ message: string }>('/api/auth/forgot-password', { email: email.value });
    sent.value = response.message;
  } catch (caught) {
    error.value = caught instanceof ApiError ? caught : null;
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <main class="mx-auto flex min-h-screen w-full max-w-sm flex-col justify-center gap-8 p-6">
    <header class="flex flex-col gap-2">
      <h1 class="text-2xl font-semibold tracking-tight text-ink">
        Forgotten password
      </h1>
      <p class="text-sm text-ink-muted">
        We will email you a link to set a new one.
      </p>
    </header>

    <p
      v-if="sent"
      data-testid="reset-link-sent"
      class="rounded-2xl bg-surface-raised p-5 text-success"
    >
      {{ sent }}
    </p>

    <form
      v-else
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
        testid="forgot-email"
        :errors="fieldErrors('email')"
      />

      <p
        v-if="error && error.kind !== 'validation'"
        data-testid="forgot-error"
        class="text-sm text-danger"
      >
        {{ error.message }}
      </p>

      <button
        type="submit"
        data-testid="submit-forgot"
        :disabled="submitting"
        class="mt-2 rounded-lg bg-accent px-4 py-3 font-semibold text-accent-ink disabled:opacity-60"
      >
        {{ submitting ? 'Sending…' : 'Send the link' }}
      </button>
    </form>

    <RouterLink
      :to="{ name: 'login' }"
      data-testid="back-to-login"
      class="text-center text-ink-muted underline underline-offset-4"
    >
      Back to log in
    </RouterLink>
  </main>
</template>
