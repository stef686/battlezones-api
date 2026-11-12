<script setup lang="ts">
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, ref, watch } from 'vue';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { amendAttendee, fetchAttendee, fetchEvent, type Allegiance } from '@/api/events';
import { keys } from '@/api/keys';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import AttendeeDetailsFields from '@/components/AttendeeDetailsFields.vue';
import BackLink from '@/components/BackLink.vue';
import MissingNotice from '@/components/MissingNotice.vue';

const props = defineProps<{ eventSlug: string; attendeeId: string }>();

const client = useApiClient();
const queryClient = useQueryClient();

const attendeeKey = computed(() => keys.attendee(props.eventSlug, Number(props.attendeeId)));

const { data: event, error: eventError } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

/**
 * A reader who does not run the Event is told the page is not there — the
 * same answer as an Attendee that does not exist, and for the same reason.
 */
const mayOrganise = computed(() => event.value?.viewer?.permissions.organise === true);

const { data: attendee, error: attendeeError } = useQuery({
  queryKey: attendeeKey,
  queryFn: () => fetchAttendee(client, props.eventSlug, Number(props.attendeeId)),
  enabled: mayOrganise,
  retry: false,
});

const missing = computed(() => (event.value !== undefined && !mayOrganise.value)
  || [eventError.value, attendeeError.value].some((caught) => caught instanceof ApiError && caught.kind === 'not_found'));

const unreadable = computed(() => (attendeeError.value ?? eventError.value) as ApiError | null);

const partyName = ref('');
const allegiance = ref('');

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

const fieldErrors = computed(() => ({
  name: failure.value?.fields['name'],
  allegiance: failure.value?.fields['allegiance'],
}));

async function save(): Promise<void> {
  saving.value = true;
  saved.value = false;
  failure.value = null;

  try {
    await amendAttendee(client, props.eventSlug, Number(props.attendeeId), {
      name: partyName.value === '' ? null : partyName.value,
      allegiance: allegiance.value === '' ? null : (allegiance.value as Allegiance),
    });

    // Refetched rather than patched in: the detail carries more than the
    // amend answers with, and the Attendee page behind this one reads it.
    await queryClient.invalidateQueries({ queryKey: attendeeKey.value });
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
      :to="{ name: 'attendee', params: { eventSlug: props.eventSlug, attendeeId: props.attendeeId } }"
      testid="back-to-attendee"
    >
      Back to the team
    </BackLink>

    <MissingNotice
      v-if="missing"
      thing="team"
    />

    <p
      v-else-if="unreadable"
      data-testid="edit-attendee-error"
      role="alert"
      class="text-destructive"
    >
      {{ unreadable.message }}
    </p>

    <template v-else-if="attendee && event">
      <h1 class="text-2xl font-bold tracking-tight text-foreground">
        Edit attendee
      </h1>

      <form
        class="flex flex-col gap-4"
        novalidate
        @submit.prevent="save"
      >
        <AttendeeDetailsFields
          v-model:name="partyName"
          v-model:allegiance="allegiance"
          :requires-allegiance="event.requires_allegiance"
          :allegiance-locked="attendee.allegiance_locked === true"
          :errors="fieldErrors"
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

    <p
      v-else
      class="text-muted-foreground-1"
    >
      Loading the team…
    </p>
  </main>
</template>
