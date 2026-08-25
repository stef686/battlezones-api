<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';

import { DEVICE_NAME, useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { claimInvite } from '@/api/invites';
import AppButton from '@/components/AppButton.vue';
import AuthCard from '@/components/AuthCard.vue';
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
  <AuthCard
    v-if="invite === null"
    title="Open your invitation again"
  >
    <p
      data-testid="claim-needs-invite"
      class="text-sm text-muted-foreground-1"
    >
      Setting a password needs the link from your invitation email. Open it again, or log in if you already have a password.
    </p>

    <AppButton
      :to="{ name: 'login' }"
      data-testid="claim-login-link"
      block
      class="mt-6"
    >
      Log in
    </AppButton>
  </AuthCard>

  <AuthCard
    v-else
    title="Set a password"
    subtitle="This keeps your account after the event, and lets you back in on another device."
  >
    <form
      class="grid gap-4"
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
        role="alert"
        class="text-sm text-destructive"
      >
        {{ error.message }}
      </p>

      <AppButton
        type="submit"
        data-testid="submit-claim"
        :disabled="submitting"
        block
        class="mt-2"
      >
        {{ submitting ? 'Saving…' : 'Save password' }}
      </AppButton>
    </form>
  </AuthCard>
</template>
