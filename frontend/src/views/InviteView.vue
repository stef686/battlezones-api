<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query';
import { computed, ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';

import { DEVICE_NAME, useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { enterWithInvite, fetchInvite } from '@/api/invites';
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
  <main class="mx-auto flex min-h-screen w-full max-w-sm flex-col justify-center gap-8 p-6">
    <p
      v-if="isPending"
      class="text-ink-muted"
    >
      Opening your invitation…
    </p>

    <template v-else-if="dead">
      <header class="flex flex-col gap-2">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">
          This invitation has run out
        </h1>
        <p
          data-testid="invite-dead"
          class="text-ink-muted"
        >
          {{ dead }}
        </p>
      </header>

      <RouterLink
        :to="{ name: 'login' }"
        data-testid="invite-login-link"
        class="rounded-lg bg-accent px-4 py-3 text-center font-semibold text-accent-ink"
      >
        Log in
      </RouterLink>
    </template>

    <p
      v-else-if="error"
      data-testid="invite-error"
      class="text-danger"
    >
      {{ (error as ApiError).message }}
    </p>

    <template v-else-if="invite">
      <header class="flex flex-col gap-2">
        <p class="text-sm uppercase tracking-widest text-ink-faint">
          You are invited to
        </p>
        <h1
          data-testid="invite-event"
          class="text-2xl font-semibold tracking-tight text-ink"
        >
          {{ invite.event.name }}
        </h1>
        <p
          v-if="dates"
          data-testid="invite-dates"
          class="text-ink-muted"
        >
          {{ dates }}
        </p>
      </header>

      <section class="flex flex-col gap-1 rounded-2xl bg-surface-raised p-5">
        <p class="text-sm text-ink-faint">
          Invitation sent to
        </p>
        <p
          data-testid="invite-email"
          class="text-ink"
        >
          {{ invite.email }}
        </p>
        <p
          data-testid="invite-role"
          class="mt-2 text-sm text-ink-muted"
        >
          You are joining as {{ invite.role }}.
        </p>
      </section>

      <div class="flex flex-col gap-3">
        <button
          type="button"
          data-testid="enter-with-invite"
          :disabled="entering"
          class="rounded-lg bg-accent px-4 py-3 font-semibold text-accent-ink disabled:opacity-60"
          @click="enter"
        >
          {{ entering ? 'Entering…' : 'Enter the event' }}
        </button>

        <button
          type="button"
          data-testid="claim-from-invite"
          class="text-ink-muted underline underline-offset-4"
          @click="claim"
        >
          Set a password now
        </button>
      </div>

      <p
        v-if="problem"
        data-testid="invite-problem"
        class="text-danger"
      >
        {{ problem }}
      </p>
    </template>
  </main>
</template>
