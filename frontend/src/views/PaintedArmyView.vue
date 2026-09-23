<script setup lang="ts">
import { computed, ref } from 'vue';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { enterPainting } from '@/api/polls';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import BackLink from '@/components/BackLink.vue';
import { useMyTeam } from '@/composables/useMyTeam';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();
const { attendee, attendeeId, paintingPoll, loading, refresh } = useMyTeam(() => props.eventSlug);

/**
 * Entering is the Player saying their army is on the display table, which is
 * theirs to say — the number beside it is not, and belongs to whoever laid the
 * table out.
 */
const entered = computed(() => attendee.value?.painting_entered === true);

const working = ref(false);
const problem = ref<string | null>(null);

async function toggle(): Promise<void> {
  if (attendeeId.value === null) {
    return;
  }

  working.value = true;
  problem.value = null;

  try {
    await enterPainting(client, props.eventSlug, attendeeId.value, !entered.value);
    await refresh();
  } catch (caught) {
    problem.value = caught instanceof ApiError ? caught.message : 'That could not be done.';
  } finally {
    working.value = false;
  }
}
</script>

<template>
  <main class="mx-auto flex w-full max-w-md flex-col gap-5 p-5">
    <BackLink
      :to="{ name: 'my-team', params: { eventSlug: props.eventSlug } }"
      testid="back-to-my-team"
    >
      Back to my team
    </BackLink>

    <p
      v-if="loading"
      class="text-muted-foreground-1"
    >
      Loading your team…
    </p>

    <template v-else-if="attendee && paintingPoll">
      <h1 class="text-2xl font-bold tracking-tight text-foreground">
        {{ paintingPoll.name }}
      </h1>

      <p
        v-if="entered"
        data-testid="painting-entered"
        class="text-sm font-medium text-success"
      >
        Your army is on the display table{{ attendee.display_number ? ` under number ${attendee.display_number}` : '' }}.
      </p>
      <p
        v-else
        class="text-sm text-muted-foreground-1"
      >
        Put your army on the display table and enter it here, so people can vote for it.
      </p>

      <AppButton
        data-testid="enter-painting"
        variant="secondary"
        :disabled="working"
        block
        @click="toggle"
      >
        {{ entered ? 'Take it out of the vote' : 'Enter my army' }}
      </AppButton>

      <AppAlert
        v-if="problem"
        data-testid="painting-problem"
        tone="error"
      >
        {{ problem }}
      </AppAlert>
    </template>
  </main>
</template>
