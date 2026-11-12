<script setup lang="ts">
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, ref, watch } from 'vue';
import { useRouter } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { amendAttendee, deleteAttendee, fetchAttendee, fetchEvent, type Allegiance } from '@/api/events';
import { keys } from '@/api/keys';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import AttendeeDetailsFields from '@/components/AttendeeDetailsFields.vue';
import BackLink from '@/components/BackLink.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import MissingNotice from '@/components/MissingNotice.vue';

const props = defineProps<{ eventSlug: string; attendeeId: string }>();

const client = useApiClient();
const queryClient = useQueryClient();
const router = useRouter();

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

const unreadable = computed(() => attendeeError.value ?? eventError.value);

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

/**
 * What was last saved, so "Saved." describes the form only while the form
 * still holds it: a keystroke afterwards is a change nobody has saved yet.
 */
const savedAs = ref<{ name: string; allegiance: string } | null>(null);
const saved = computed(() => savedAs.value?.name === partyName.value
  && savedAs.value.allegiance === allegiance.value);

const saving = ref(false);
const failure = ref<ApiError | null>(null);

const fieldErrors = computed(() => ({
  name: failure.value?.fields['name'],
  allegiance: failure.value?.fields['allegiance'],
}));

async function save(): Promise<void> {
  const submitted = { name: partyName.value, allegiance: allegiance.value };

  saving.value = true;
  savedAs.value = null;
  failure.value = null;

  try {
    await amendAttendee(client, props.eventSlug, Number(props.attendeeId), {
      name: partyName.value === '' ? null : partyName.value,
      allegiance: allegiance.value === '' ? null : (allegiance.value as Allegiance),
    });

    // Refetched rather than patched in: the detail carries more than the
    // amend answers with. The list pages and the Standings name the team
    // too, and would otherwise show the old name until their next read.
    await Promise.all([
      queryClient.invalidateQueries({ queryKey: keys.allAttendees(props.eventSlug) }),
      queryClient.invalidateQueries({ queryKey: keys.standings(props.eventSlug) }),
    ]);
    savedAs.value = submitted;
  } catch (caught) {
    failure.value = caught instanceof ApiError ? caught : null;
  } finally {
    saving.value = false;
  }
}

/**
 * Deleting is for a team that has never been paired — a no-show, a duplicate,
 * a refund. Once it is in any Game the API refuses, and a Drop is the tool,
 * so the button is offered only while the API says it would be accepted.
 */
const mayDelete = computed(() => attendee.value?.has_been_paired === false);

const confirmingDelete = ref(false);
const deleting = ref(false);
const deleteFailure = ref<string | null>(null);

async function remove(): Promise<void> {
  deleting.value = true;
  deleteFailure.value = null;

  try {
    await deleteAttendee(client, props.eventSlug, Number(props.attendeeId));

    // Forgotten rather than refetched: the team is gone, and asking after it
    // again would only answer "not found". Everything that counted or named
    // it is behind now.
    queryClient.removeQueries({ queryKey: attendeeKey.value });
    await Promise.all([
      queryClient.invalidateQueries({ queryKey: keys.allAttendees(props.eventSlug) }),
      queryClient.invalidateQueries({ queryKey: keys.standings(props.eventSlug) }),
      queryClient.invalidateQueries({ queryKey: keys.event(props.eventSlug), exact: true }),
    ]);

    await router.push({ name: 'attendees', params: { eventSlug: props.eventSlug } });
  } catch (caught) {
    deleteFailure.value = caught instanceof Error ? caught.message : 'That could not be deleted.';
  } finally {
    deleting.value = false;
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

      <section
        v-if="mayDelete"
        class="flex flex-col gap-3 border-t border-card-divider pt-5"
      >
        <AppButton
          data-testid="delete-attendee"
          variant="danger"
          block
          :disabled="deleting"
          @click="confirmingDelete = true"
        >
          Delete
        </AppButton>

        <AppAlert
          v-if="deleteFailure"
          data-testid="delete-attendee-error"
          tone="error"
        >
          {{ deleteFailure }}
        </AppAlert>

        <ConfirmDialog
          v-model:open="confirmingDelete"
          :title="`Delete ${attendee.name}?`"
          confirm-label="Delete"
          testid="delete-attendee-confirm"
          @confirm="remove"
        >
          This removes them from the event and can't be undone.
        </ConfirmDialog>
      </section>
    </template>

    <p
      v-else
      class="text-muted-foreground-1"
    >
      Loading the team…
    </p>
  </main>
</template>
