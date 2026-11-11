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
import {
  fetchEvent,
  removeBanner,
  updateEvent,
  uploadBanner,
  type EventChanges,
  type EventSummary,
} from '@/api/events';
import { keys } from '@/api/keys';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import BackLink from '@/components/BackLink.vue';
import EventBannerField from '@/components/EventBannerField.vue';
import EventColourField from '@/components/EventColourField.vue';
import EventDetailsFields from '@/components/EventDetailsFields.vue';
import EventVenueFields from '@/components/EventVenueFields.vue';
import MissingNotice from '@/components/MissingNotice.vue';
import TextField from '@/components/TextField.vue';
import TimezoneField from '@/components/TimezoneField.vue';
import { reanchored } from '@/lib/dates';
import type { EventDetails, EventVenue } from '@/lib/event-fields';

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
  details: EventDetails;
  description: string;
  registration_closes_at: string;
  venue: EventVenue;
  colour: string | null;
  timezone: string;
}

function formOf(loaded: EventSummary): Form {
  return {
    details: {
      name: loaded.name,
      starts_at: localMoment(loaded.starts_at),
      ends_at: localMoment(loaded.ends_at),
    },
    description: loaded.description ?? '',
    registration_closes_at: localMoment(loaded.registration_closes_at),
    venue: {
      venue_name: loaded.venue.name ?? '',
      venue_address: loaded.venue.address ?? '',
      venue_city: loaded.venue.city ?? '',
      venue_country: loaded.venue.country ?? '',
    },
    colour: loaded.colour ?? null,
    timezone: loaded.timezone,
  };
}

/** The form as the PATCH names its fields, so the two can be compared field by field. */
function fieldsOf(edited: Form): EventDetails & EventVenue & Pick<Form, 'description' | 'registration_closes_at'> {
  return {
    ...edited.details,
    ...edited.venue,
    description: edited.description,
    registration_closes_at: edited.registration_closes_at,
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
  if (form.value === null || saved.value === null) {
    return {};
  }

  const current = fieldsOf(form.value);
  const original = fieldsOf(saved.value);

  const changed: EventChanges = {};

  if (current.name !== original.name) {
    changed.name = current.name;
  }

  for (const field of ['description', 'venue_name', 'venue_address', 'venue_city', 'venue_country'] as const) {
    if (current[field] !== original[field]) {
      changed[field] = current[field] === '' ? null : current[field];
    }
  }

  if (form.value.colour !== saved.value.colour) {
    changed.colour = form.value.colour;
  }


  for (const field of ['starts_at', 'ends_at', 'registration_closes_at'] as const) {
    if (current[field] !== original[field]) {
      changed[field] = current[field] === '' ? null : `${current[field]}:00Z`;
    }
  }

  // A new zone keeps the Event's own times on the same clock: an Event that
  // opened at 10:00 in London opens at 10:00 in New York, rather than at
  // 05:00 because the moment stood still. So every time it has is sent
  // again, re-anchored, whether or not it was edited.
  if (form.value.timezone !== saved.value.timezone) {
    changed.timezone = form.value.timezone;

    for (const field of ['starts_at', 'ends_at', 'registration_closes_at'] as const) {
      if (current[field] !== '') {
        changed[field] = reanchored(`${current[field]}:00Z`, saved.value.timezone, form.value.timezone);
      }
    }
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
 * Why the timezone can no longer move, once it cannot. Every Schedule block's
 * day is read in the zone, so the API refuses a new one after the first block
 * (ADR 0002 amendment); saying so here spares the Organiser finding out from
 * a refusal.
 */
const timezoneFixedReason = computed(() => (event.value?.timezone_is_fixed === true
  ? 'The timezone is fixed now the event has a schedule: every block\'s time is written in it.'
  : null));

const banner = computed(() => event.value?.banner ?? null);
const bannerBusy = ref(false);

/**
 * The Banner is uploaded on choosing a file rather than on saving the form.
 * It is a separate multipart route — a PATCH body carries no files — and
 * pretending otherwise would mean one Save button that sometimes made two
 * requests and could half-succeed.
 */
async function chooseBanner(file: File): Promise<void> {
  if (!bannerBusy.value) {
    await withBanner(() => uploadBanner(client, props.eventSlug, file));
  }
}

async function dropBanner(): Promise<void> {
  if (!bannerBusy.value) {
    await withBanner(() => removeBanner(client, props.eventSlug));
  }
}

async function withBanner(change: () => Promise<EventSummary>): Promise<void> {
  bannerBusy.value = true;
  problem.value = null;
  errors.value = {};

  try {
    queryClient.setQueryData(keys.event(props.eventSlug), await change());
  } catch (caught) {
    if (caught instanceof ApiError && caught.kind === 'validation') {
      errors.value = caught.fields;
    } else {
      problem.value = caught instanceof ApiError ? caught.message : 'That image could not be used.';
    }
  } finally {
    bannerBusy.value = false;
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
      <BackLink
        :to="{ name: 'organise', params: { eventSlug: props.eventSlug } }"
        testid="back-to-organise"
      >
        Back to running the event
      </BackLink>

      <header>
        <h1 class="text-2xl font-bold tracking-tight text-foreground">
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

      <EventBannerField
        :banner="banner"
        :busy="bannerBusy"
        :errors="errors"
        @choose="chooseBanner"
        @remove="dropBanner"
      />

      <form
        data-testid="settings-save"
        class="flex flex-col gap-5"
        @submit.prevent="save"
      >
        <EventColourField
          v-model="form.colour"
          :errors="errors"
        />

        <EventDetailsFields
          v-model="form.details"
          :errors="errors"
        >
          <TextField
            v-model="form.description"
            label="Description"
            testid="settings-description"
            :errors="errors.description"
          />
        </EventDetailsFields>

        <TimezoneField
          v-model="form.timezone"
          :fixed-reason="timezoneFixedReason"
          hint="Changing it keeps the event's times on the same clock in the new zone."
          :errors="errors.timezone"
        />

        <TextField
          v-model="form.registration_closes_at"
          label="Entry closes"
          type="datetime-local"
          testid="settings-registration-closes-at"
          :errors="errors.registration_closes_at"
        />

        <EventVenueFields
          v-model="form.venue"
          :errors="errors"
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
