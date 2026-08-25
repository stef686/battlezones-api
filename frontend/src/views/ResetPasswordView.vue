<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import { DEVICE_NAME, useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import AppButton from '@/components/AppButton.vue';
import AuthCard from '@/components/AuthCard.vue';
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
  <AuthCard
    v-if="token === null || email === null"
    title="This link is incomplete"
  >
    <p
      data-testid="reset-link-broken"
      class="text-sm text-muted-foreground-1"
    >
      Open the link from the email again, in full. Some mail apps cut long links short.
    </p>

    <AppButton
      :to="{ name: 'forgot-password' }"
      data-testid="request-new-reset"
      block
      class="mt-6"
    >
      Send a new link
    </AppButton>
  </AuthCard>

  <AuthCard
    v-else
    title="Set a new password"
  >
    <p
      data-testid="reset-email"
      class="-mt-4 mb-6 text-center text-sm text-muted-foreground-1"
    >
      For {{ email }}.
    </p>

    <form
      class="grid gap-4"
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
        role="alert"
        class="text-sm text-destructive"
      >
        {{ message }}
      </p>

      <p
        v-if="error && error.kind !== 'validation'"
        data-testid="reset-error"
        role="alert"
        class="text-sm text-destructive"
      >
        {{ error.message }}
      </p>

      <AppButton
        type="submit"
        data-testid="submit-reset"
        :disabled="submitting"
        block
        class="mt-2"
      >
        {{ submitting ? 'Saving…' : 'Save password' }}
      </AppButton>
    </form>
  </AuthCard>
</template>
