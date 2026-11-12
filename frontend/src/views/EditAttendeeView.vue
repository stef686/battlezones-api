<script setup lang="ts">
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, ref, watch } from 'vue';
import { useRouter } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { amendAttendee, deleteAttendee, dropAttendee, fetchAttendee, fetchEvent, undropAttendee, type Allegiance, type Attendee } from '@/api/events';
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
  } catch (caught) {
    deleteFailure.value = caught instanceof Error ? caught.message : 'That could not be deleted.';

    return;
  } finally {
    deleting.value = false;
  }

  // Outside the try on purpose: once the delete has landed, nothing that
  // goes wrong tidying up may report the team as not deleted. Its own read is
  // forgotten rather than refetched, since asking after a deleted team only
  // answers "not found"; everything that counted or named it is refreshed.
  queryClient.removeQueries({ queryKey: attendeeKey.value });
  void queryClient.invalidateQueries({ queryKey: keys.allAttendees(props.eventSlug) });
  void queryClient.invalidateQueries({ queryKey: keys.standings(props.eventSlug) });
  void queryClient.invalidateQueries({ queryKey: keys.event(props.eventSlug), exact: true });

  await router.push({ name: 'attendees', params: { eventSlug: props.eventSlug } });
}

/**
 * Dropping is for a team that has been paired and has left. Before its first
 * Game the API points at Delete instead, and a team already Dropped has
 * nothing more to drop from.
 */
const mayDrop = computed(() => attendee.value?.has_been_paired === true && !attendee.value.dropped_at);

/** A Dropped team offers only the way back, and asks nothing: undoing is not destructive. */
const mayUndrop = computed(() => Boolean(attendee.value?.dropped_at));

const confirmingDrop = ref(false);
const dropping = ref(false);
const dropFailure = ref<string | null>(null);

function drop(): Promise<void> {
  return changeDrop(() => dropAttendee(client, props.eventSlug, Number(props.attendeeId)));
}

function undrop(): Promise<void> {
  return changeDrop(() => undropAttendee(client, props.eventSlug, Number(props.attendeeId)));
}

/** Drop and Undrop are one switch, so they share its busy state and its one place to fail. */
async function changeDrop(change: () => Promise<Attendee>): Promise<void> {
  dropping.value = true;
  dropFailure.value = null;

  try {
    queryClient.setQueryData(attendeeKey.value, await change());
  } catch (caught) {
    dropFailure.value = caught instanceof Error ? caught.message : 'That could not be changed.';

    return;
  } finally {
    dropping.value = false;
  }

  // The Attendee lists carry `dropped_at`, so every page of them is behind.
  void queryClient.invalidateQueries({ queryKey: keys.allAttendees(props.eventSlug) });
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
        v-if="mayDelete || mayDrop || mayUndrop"
        class="flex flex-col gap-3 border-t border-card-divider pt-5"
      >
        <!-- One button at a time: Delete until the team has been paired,
             Drop afterwards, and Undrop once it has been Dropped. -->
        <AppButton
          v-if="mayUndrop"
          data-testid="undrop-attendee"
          variant="secondary"
          block
          :disabled="dropping"
          @click="undrop"
        >
          Undrop
        </AppButton>

        <AppButton
          v-if="mayDrop"
          data-testid="drop-attendee"
          variant="danger"
          block
          :disabled="dropping"
          @click="confirmingDrop = true"
        >
          Drop
        </AppButton>

        <AppAlert
          v-if="dropFailure"
          data-testid="drop-attendee-error"
          tone="error"
        >
          {{ dropFailure }}
        </AppAlert>

        <ConfirmDialog
          v-if="mayDrop"
          v-model:open="confirmingDrop"
          :title="`Drop ${attendee.name || 'this team'}?`"
          confirm-label="Drop"
          testid="drop-attendee-confirm"
          @confirm="drop"
        >
          <p>They won't be paired in later rounds. Results so far stand.</p>

          <!-- A Drop does not reach into a Round already drafted, and there
               is no taking them out of it yet: said now, not discovered when
               the Round is published with an empty chair in it. -->
          <p
            v-if="attendee.paired_in_draft_round"
            data-testid="drop-attendee-draft-round"
            class="mt-2"
          >
            They are still paired in the round being drafted, and dropping them won't change that.
          </p>
        </ConfirmDialog>

        <AppButton
          v-if="mayDelete"
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
          v-if="mayDelete"
          v-model:open="confirmingDelete"
          :title="`Delete ${attendee.name || 'this team'}?`"
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
