<script setup lang="ts">
import { computed, ref, watch } from 'vue';

import { useApiClient } from '@/api';
import { submitArmyList } from '@/api/army-lists';
import { ApiError } from '@/api/errors';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import BackLink from '@/components/BackLink.vue';
import { useMyTeam } from '@/composables/useMyTeam';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();
const { attendee, me, loading, refresh } = useMyTeam(() => props.eventSlug);

const armyList = ref('');

watch(me, (member) => {
  armyList.value = member?.army_list ?? '';
}, { immediate: true });

/**
 * Locked means submitted: the API refuses an edit and only an Organiser can
 * reopen it, so the screen has to say which of the two states a Player is in
 * rather than leaving them to guess from an empty box.
 */
const listLocked = computed(() => me.value?.army_list_locked === true);

const submitting = ref(false);
const problem = ref<string | null>(null);

async function submit(): Promise<void> {
  submitting.value = true;
  problem.value = null;

  try {
    await submitArmyList(client, props.eventSlug, armyList.value);
    await refresh();
  } catch (caught) {
    problem.value = caught instanceof ApiError ? caught.message : 'That could not be sent.';
  } finally {
    submitting.value = false;
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

    <template v-else-if="attendee">
      <h1 class="text-2xl font-bold tracking-tight text-foreground">
        My list
      </h1>

      <template v-if="listLocked">
        <AppAlert
          data-testid="army-list-locked"
          tone="success"
        >
          Submitted and locked. Ask an organiser to reopen it if it needs correcting.
        </AppAlert>
        <p
          data-testid="army-list-mine"
          class="whitespace-pre-wrap text-sm text-foreground"
        >
          {{ me?.army_list }}
        </p>
      </template>

      <template v-else>
        <textarea
          v-model="armyList"
          data-testid="army-list"
          rows="12"
          placeholder="Detachments, units, wargear…"
          class="block w-full rounded-lg border border-border bg-background-2 px-4 py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-hidden"
        />
        <p class="text-sm text-muted-foreground-1">
          Submitting locks the list. Only an organiser can reopen it.
        </p>
        <AppButton
          data-testid="submit-army-list"
          :disabled="submitting"
          block
          @click="submit"
        >
          {{ submitting ? 'Sending…' : 'Submit list' }}
        </AppButton>
      </template>

      <AppAlert
        v-if="problem"
        data-testid="army-list-problem"
        tone="error"
      >
        {{ problem }}
      </AppAlert>
    </template>
  </main>
</template>
