<script setup lang="ts">
import { ref } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import AuthCard from '@/components/AuthCard.vue';
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
  <AuthCard
    title="Forgotten password"
    subtitle="We will email you a link to set a new one."
  >
    <!-- The API answers the same way whether or not the address is on an
         account, so the screen states its own message rather than a fact
         about the address. -->
    <AppAlert
      v-if="sent"
      data-testid="reset-link-sent"
      tone="success"
    >
      {{ sent }}
    </AppAlert>

    <form
      v-else
      class="grid gap-4"
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
        role="alert"
        class="text-sm text-destructive"
      >
        {{ error.message }}
      </p>

      <AppButton
        type="submit"
        data-testid="submit-forgot"
        :disabled="submitting"
        block
        class="mt-2"
      >
        {{ submitting ? 'Sending…' : 'Send the link' }}
      </AppButton>
    </form>

    <template #footer>
      <RouterLink
        :to="{ name: 'login' }"
        data-testid="back-to-login"
        class="text-sm font-medium text-primary decoration-2 hover:underline focus:underline focus:outline-hidden"
      >
        Back to log in
      </RouterLink>
    </template>
  </AuthCard>
</template>
