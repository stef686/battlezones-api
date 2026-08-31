<script setup lang="ts">
import { computed, ref, watch } from 'vue';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { amendAttendee, type Allegiance } from '@/api/events';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import BackLink from '@/components/BackLink.vue';
import SelectField from '@/components/SelectField.vue';
import TextField from '@/components/TextField.vue';
import { useMyTeam } from '@/composables/useMyTeam';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();
const { event, attendee, attendeeId, loading, refresh } = useMyTeam(() => props.eventSlug);

const ALLEGIANCES: { value: Allegiance; label: string }[] = [
  { value: 'loyalist', label: 'Loyalist' },
  { value: 'traitor', label: 'Traitor' },
];

const partyName = ref('');
const allegiance = ref('');

/**
 * Allegiance is a pairing constraint, so it is frozen once the Event is under
 * way — Games already played were paired on it. The field closes rather than
 * accepting a change the API would refuse: being told afterwards is a worse
 * way to learn it than seeing it was never on offer.
 */
const allegianceLocked = computed(() => attendee.value?.allegiance_locked === true);

// The form mirrors what the API last returned rather than holding its own
// idea of the team: a correction made on another device wins on the next read.
watch(attendee, (loaded) => {
  if (loaded === undefined) {
    return;
  }

  partyName.value = loaded.name ?? '';
  allegiance.value = loaded.allegiance ?? '';
}, { immediate: true });

const saving = ref(false);
const saved = ref(false);
const failure = ref<ApiError | null>(null);

function fieldErrors(field: string): string[] {
  return failure.value?.fields[field] ?? [];
}

async function save(): Promise<void> {
  if (attendeeId.value === null) {
    return;
  }

  saving.value = true;
  saved.value = false;
  failure.value = null;

  try {
    await amendAttendee(client, props.eventSlug, attendeeId.value, {
      name: partyName.value === '' ? null : partyName.value,
      allegiance: allegiance.value === '' ? null : (allegiance.value as Allegiance),
    });

    await refresh();
    saved.value = true;
  } catch (caught) {
    failure.value = caught instanceof ApiError ? caught : null;
  } finally {
    saving.value = false;
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

    <template v-else-if="attendee && event">
      <h1 class="text-2xl font-bold tracking-tight text-foreground">
        Team details
      </h1>

      <form
        class="flex flex-col gap-4"
        novalidate
        @submit.prevent="save"
      >
        <TextField
          v-model="partyName"
          label="Team name"
          testid="team-name-field"
          :errors="fieldErrors('name')"
        />

        <SelectField
          v-if="event.requires_allegiance"
          v-model="allegiance"
          label="Allegiance"
          placeholder="Choose a side"
          testid="team-allegiance"
          :options="ALLEGIANCES"
          :disabled="allegianceLocked"
          :hint="allegianceLocked ? 'Frozen now the event has begun.' : 'Frozen once the event begins.'"
          :errors="fieldErrors('allegiance')"
        />

        <AppAlert
          v-if="failure && failure.kind !== 'validation'"
          data-testid="team-error"
          tone="error"
        >
          {{ failure.message }}
        </AppAlert>

        <AppAlert
          v-if="saved"
          data-testid="team-saved"
          tone="success"
        >
          Saved.
        </AppAlert>

        <AppButton
          type="submit"
          data-testid="save-team"
          :disabled="saving"
          block
          class="mt-2"
        >
          {{ saving ? 'Saving…' : 'Save' }}
        </AppButton>
      </form>
    </template>
  </main>
</template>
