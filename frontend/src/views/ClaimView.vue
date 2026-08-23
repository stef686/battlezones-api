<script setup lang="ts">
import { computed, ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';

import { DEVICE_NAME, useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { claimInvite } from '@/api/invites';
import TextField from '@/components/TextField.vue';
import { useSessionStore } from '@/stores/session';

const client = useApiClient();
const session = useSessionStore();
const router = useRouter();

/**
 * Claiming needs the Invite token, which only the invitation link carries. A
 * session that has lost it cannot set a password here, and saying so is more
 * use than a form that will always fail.
 */
const invite = computed(() => session.invite);

const password = ref('');
const passwordConfirmation = ref('');
const submitting = ref(false);
const error = ref<ApiError | null>(null);

function fieldErrors(field: string): string[] {
  return error.value?.fields[field] ?? [];
}

async function submit(): Promise<void> {
  const remembered = invite.value;

  if (remembered === null) {
    return;
  }

  submitting.value = true;
  error.value = null;

  try {
    await claimInvite(client, remembered.token, {
      password: password.value,
      passwordConfirmation: passwordConfirmation.value,
      deviceName: DEVICE_NAME,
    });

    // The Invite is spent now — claiming revokes it — and the account is a
    // real one, so nothing is left to confine the session to its Event.
    session.forgetInvite();
    await session.load(client);

    await router.replace({ name: 'my-game', params: { eventSlug: remembered.eventSlug } });
  } catch (caught) {
    error.value = caught instanceof ApiError ? caught : null;
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <main class="mx-auto flex min-h-screen w-full max-w-sm flex-col justify-center gap-8 p-6">
    <template v-if="invite === null">
      <header class="flex flex-col gap-2">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">
          Open your invitation again
        </h1>
        <p
          data-testid="claim-needs-invite"
          class="text-ink-muted"
        >
          Setting a password needs the link from your invitation email. Open it again, or log in if you already have a password.
        </p>
      </header>

      <RouterLink
        :to="{ name: 'login' }"
        data-testid="claim-login-link"
        class="rounded-lg bg-accent px-4 py-3 text-center font-semibold text-accent-ink"
      >
        Log in
      </RouterLink>
    </template>

    <template v-else>
      <header class="flex flex-col gap-2">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">
          Set a password
        </h1>
        <p class="text-sm text-ink-muted">
          This keeps your account after the event, and lets you back in on another device.
        </p>
      </header>

      <form
        class="flex flex-col gap-4"
        novalidate
        @submit.prevent="submit"
      >
        <TextField
          v-model="password"
          label="Password"
          type="password"
          autocomplete="new-password"
          testid="claim-password"
          hint="At least 8 characters."
          :errors="fieldErrors('password')"
        />

        <TextField
          v-model="passwordConfirmation"
          label="Confirm password"
          type="password"
          autocomplete="new-password"
          testid="claim-password-confirmation"
          :errors="fieldErrors('password_confirmation')"
        />

        <p
          v-if="error && error.kind !== 'validation'"
          data-testid="claim-error"
          class="text-sm text-danger"
        >
          {{ error.message }}
        </p>

        <button
          type="submit"
          data-testid="submit-claim"
          :disabled="submitting"
          class="mt-2 rounded-lg bg-accent px-4 py-3 font-semibold text-accent-ink disabled:opacity-60"
        >
          {{ submitting ? 'Saving…' : 'Save password' }}
        </button>
      </form>
    </template>
  </main>
</template>
