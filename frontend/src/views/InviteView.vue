<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query';
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';

import { DEVICE_NAME, useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { enterWithInvite, fetchInvite } from '@/api/invites';
import AppButton from '@/components/AppButton.vue';
import AuthCard from '@/components/AuthCard.vue';
import { formatDateRange } from '@/lib/dates';
import { useSessionStore } from '@/stores/session';

const props = defineProps<{ token: string }>();

const client = useApiClient();
const session = useSessionStore();
const router = useRouter();

/**
 * The invitation is read before anything is asked of the reader. Someone who
 * has been handed a link needs to see whose Event it is and which address it
 * was sent to before they will believe it, and certainly before they type a
 * password into it.
 */
const { data: invite, isPending, error } = useQuery({
  queryKey: ['invite', props.token],
  queryFn: () => fetchInvite(client, props.token),
  retry: false,
});

const dates = computed(() => formatDateRange(invite.value?.event.starts_at ?? null, invite.value?.event.ends_at ?? null));

/** A dead Invite is a state to explain, not a failure to report. */
const dead = computed(() => {
  const failure = error.value;

  if (!(failure instanceof ApiError)) {
    return null;
  }

  return failure.kind === 'gone' || failure.kind === 'not_found' ? failure.message : null;
});

const entering = ref(false);
const problem = ref<string | null>(null);

async function enter(): Promise<void> {
  if (invite.value === undefined) {
    return;
  }

  entering.value = true;
  problem.value = null;

  const eventSlug = invite.value.event.slug;

  try {
    await enterWithInvite(client, props.token, DEVICE_NAME);

    // Remembered before the profile is loaded: from here on the guard needs
    // to know which Event this session belongs to, and the token is the only
    // way back to the Claim screen.
    session.rememberInvite({ token: props.token, eventSlug });
    await session.load(client);

    await router.replace({ name: 'my-game', params: { eventSlug } });
  } catch (caught) {
    problem.value = caught instanceof ApiError ? caught.message : 'That could not be sent.';
  } finally {
    entering.value = false;
  }
}

function claim(): void {
  if (invite.value === undefined) {
    return;
  }

  session.rememberInvite({ token: props.token, eventSlug: invite.value.event.slug });

  void router.push({ name: 'claim' });
}
</script>

<template>
  <AuthCard
    v-if="isPending"
    title="Battlezones"
  >
    <p class="text-center text-sm text-muted-foreground-1">
      Opening your invitation…
    </p>
  </AuthCard>

  <AuthCard
    v-else-if="dead"
    title="This invitation has run out"
  >
    <p
      data-testid="invite-dead"
      class="text-sm text-muted-foreground-1"
    >
      {{ dead }}
    </p>

    <AppButton
      :to="{ name: 'login' }"
      data-testid="invite-login-link"
      block
      class="mt-6"
    >
      Log in
    </AppButton>
  </AuthCard>

  <AuthCard
    v-else-if="error"
    title="Battlezones"
  >
    <p
      data-testid="invite-error"
      role="alert"
      class="text-sm text-destructive"
    >
      {{ (error as ApiError).message }}
    </p>
  </AuthCard>

  <AuthCard
    v-else-if="invite"
    title="You are invited"
  >
    <!-- Whose Event it is, and which address it was sent to, before anything
         is asked of the reader: somebody handed a link needs to recognise it
         before they will type a password into it. -->
    <div class="mb-4 text-center">
      <p
        data-testid="invite-event"
        class="text-lg font-semibold text-foreground"
      >
        {{ invite.event.name }}
      </p>
      <p
        v-if="dates"
        data-testid="invite-dates"
        class="mt-1 text-sm text-muted-foreground-1"
      >
        {{ dates }}
      </p>
    </div>

    <div class="rounded-lg border border-border p-4">
      <p class="text-xs font-medium uppercase tracking-widest text-muted-foreground">
        Invitation sent to
      </p>
      <p
        data-testid="invite-email"
        class="mt-1 text-sm text-foreground"
      >
        {{ invite.email }}
      </p>
      <p
        data-testid="invite-role"
        class="mt-2 text-sm text-muted-foreground-1"
      >
        You are joining as {{ invite.role }}.
      </p>
    </div>

    <div class="mt-6 grid gap-3">
      <AppButton
        data-testid="enter-with-invite"
        :disabled="entering"
        block
        @click="enter"
      >
        {{ entering ? 'Entering…' : 'Enter the event' }}
      </AppButton>

      <AppButton
        data-testid="claim-from-invite"
        variant="ghost"
        block
        @click="claim"
      >
        Set a password now
      </AppButton>
    </div>

    <p
      v-if="problem"
      data-testid="invite-problem"
      role="alert"
      class="mt-4 text-sm text-destructive"
    >
      {{ problem }}
    </p>
  </AuthCard>
</template>
