<script setup lang="ts">
/**
 * The Event as its Organiser can change it.
 *
 * Kept off the round-running screen on purpose: settings are touched once
 * before an Event and pairings every ten minutes during it, so the two do not
 * belong under the same thumb.
 *
 * Only what changed is sent. The endpoint is a PATCH over an explicit
 * allowlist, and sending the whole form back would mean rewriting fields the
 * Organiser never touched — including ones another Organiser edited while
 * this form sat open.
 */
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, ref, watch } from 'vue';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchEvent, updateEvent, type EventChanges, type EventSummary } from '@/api/events';
import { keys } from '@/api/keys';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import MissingNotice from '@/components/MissingNotice.vue';
import TextField from '@/components/TextField.vue';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();
const queryClient = useQueryClient();

const { data: event, isPending } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

/**
 * A reader without the permission is told the page is not there — the same
 * answer as an Event that does not exist, and for the same reason.
 */
const mayOrganise = computed(() => event.value?.viewer?.permissions.organise === true);
const forbidden = computed(() => event.value !== undefined && !mayOrganise.value);

interface Form {
  name: string;
  description: string;
  venue_name: string;
  venue_address: string;
  venue_city: string;
  venue_country: string;
  starts_at: string;
  ends_at: string;
  registration_closes_at: string;
  max_attendees: string;
}

function formOf(loaded: EventSummary): Form {
  return {
    name: loaded.name,
    description: loaded.description ?? '',
    venue_name: loaded.venue.name ?? '',
    venue_address: loaded.venue.address ?? '',
    venue_city: loaded.venue.city ?? '',
    venue_country: loaded.venue.country ?? '',
    starts_at: localMoment(loaded.starts_at),
    ends_at: localMoment(loaded.ends_at),
    registration_closes_at: localMoment(loaded.registration_closes_at),
    max_attendees: loaded.max_attendees === null ? '' : String(loaded.max_attendees),
  };
}

const form = ref<Form | null>(null);
const saved = ref<Form | null>(null);

watch(event, (loaded) => {
  if (loaded === undefined) {
    return;
  }

  // Only while the form is untouched: an Organiser mid-edit must not have
  // their typing replaced by a background refetch.
  if (form.value === null || dirty.value === false) {
    form.value = formOf(loaded);
    saved.value = formOf(loaded);
  }
}, { immediate: true });

const changes = computed<EventChanges>(() => {
  const current = form.value;
  const original = saved.value;

  if (current === null || original === null) {
    return {};
  }

  const changed: EventChanges = {};

  if (current.name !== original.name) {
    changed.name = current.name;
  }

  for (const field of ['description', 'venue_name', 'venue_address', 'venue_city', 'venue_country'] as const) {
    if (current[field] !== original[field]) {
      changed[field] = current[field] === '' ? null : current[field];
    }
  }

  for (const field of ['starts_at', 'ends_at', 'registration_closes_at'] as const) {
    if (current[field] !== original[field]) {
      changed[field] = current[field] === '' ? null : `${current[field]}:00Z`;
    }
  }

  if (current.max_attendees !== original.max_attendees) {
    changed.max_attendees = current.max_attendees === '' ? null : Number(current.max_attendees);
  }

  return changed;
});

const dirty = computed(() => Object.keys(changes.value).length > 0);

const saving = ref(false);
const problem = ref<string | null>(null);
const errors = ref<Record<string, string[]>>({});
const done = ref(false);

async function save(): Promise<void> {
  if (form.value === null || saving.value) {
    return;
  }

  saving.value = true;
  problem.value = null;
  errors.value = {};
  done.value = false;

  try {
    const updated = await updateEvent(client, props.eventSlug, changes.value);

    queryClient.setQueryData(keys.event(props.eventSlug), updated);
    saved.value = formOf(updated);
    form.value = formOf(updated);
    done.value = true;
  } catch (caught) {
    if (caught instanceof ApiError && caught.kind === 'validation') {
      errors.value = caught.fields;
      problem.value = null;
    } else {
      problem.value = caught instanceof ApiError ? caught.message : 'Those settings could not be saved.';
    }
  } finally {
    saving.value = false;
  }
}

/**
 * The API keeps these in UTC and a datetime-local input has no zone of its
 * own, so the stored moment is shown exactly as stored and handed back the
 * same way. Anything cleverer would move an Event by an hour on the day the
 * clocks change.
 */
function localMoment(iso: string | null): string {
  return iso === null ? '' : iso.slice(0, 16);
}
</script>

<template>
  <main class="mx-auto flex w-full max-w-md flex-col gap-5 p-5">
    <p
      v-if="isPending"
      class="text-muted-foreground-1"
    >
      Loading…
    </p>

    <MissingNotice
      v-else-if="forbidden || event === undefined"
      thing="page"
    />

    <template v-else-if="form">
      <header>
        <p class="text-xs font-medium uppercase tracking-widest text-muted-foreground">
          {{ event.name }}
        </p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">
          Event settings
        </h1>
      </header>

      <AppAlert
        v-if="problem"
        tone="error"
      >
        {{ problem }}
      </AppAlert>

      <AppAlert
        v-if="done"
        data-testid="settings-saved"
        tone="success"
      >
        Saved.
      </AppAlert>

      <form
        data-testid="settings-save"
        class="flex flex-col gap-5"
        @submit.prevent="save"
      >
        <TextField
          v-model="form.name"
          label="Name"
          testid="settings-name"
          :errors="errors.name"
        />

        <TextField
          v-model="form.description"
          label="Description"
          testid="settings-description"
          :errors="errors.description"
        />

        <TextField
          v-model="form.starts_at"
          label="Starts"
          type="datetime-local"
          testid="settings-starts-at"
          :errors="errors.starts_at"
        />

        <TextField
          v-model="form.ends_at"
          label="Ends"
          type="datetime-local"
          testid="settings-ends-at"
          :errors="errors.ends_at"
        />

        <TextField
          v-model="form.registration_closes_at"
          label="Entry closes"
          type="datetime-local"
          testid="settings-registration-closes-at"
          :errors="errors.registration_closes_at"
        />

        <TextField
          v-model="form.max_attendees"
          label="Places"
          inputmode="numeric"
          hint="Leave empty for no limit. Never fewer than have already entered."
          testid="settings-max-attendees"
          :errors="errors.max_attendees"
        />

        <TextField
          v-model="form.venue_name"
          label="Venue"
          testid="settings-venue-name"
          :errors="errors.venue_name"
        />

        <TextField
          v-model="form.venue_address"
          label="Address"
          testid="settings-venue-address"
          :errors="errors.venue_address"
        />

        <TextField
          v-model="form.venue_city"
          label="Town or city"
          testid="settings-venue-city"
          :errors="errors.venue_city"
        />

        <TextField
          v-model="form.venue_country"
          label="Country"
          hint="Two-letter country code, such as GB."
          testid="settings-venue-country"
          :errors="errors.venue_country"
        />

        <AppButton
          type="submit"
          :disabled="!dirty || saving"
          block
        >
          {{ saving ? 'Saving…' : 'Save settings' }}
        </AppButton>
      </form>
    </template>
  </main>
</template>
