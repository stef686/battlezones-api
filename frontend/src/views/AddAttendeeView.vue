<script setup lang="ts">
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, ref, watch } from 'vue';
import { useRouter } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchEvent, fetchFactions, registerAttendee, type Allegiance } from '@/api/events';
import { keys } from '@/api/keys';
import AppButton from '@/components/AppButton.vue';
import BackLink from '@/components/BackLink.vue';
import MissingNotice from '@/components/MissingNotice.vue';
import PartyFields, { type PartyPlayer } from '@/components/PartyFields.vue';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();
const queryClient = useQueryClient();
const router = useRouter();

const { data: event, error: eventError } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

/**
 * A reader who does not run the Event is told the page is not there — the
 * same answer as an Event that does not exist, and for the same reason.
 */
const mayOrganise = computed(() => event.value?.viewer?.permissions.organise === true);

const { data: factions } = useQuery({
  queryKey: computed(() => keys.factions(props.eventSlug)),
  queryFn: () => fetchFactions(client, props.eventSlug),
  enabled: mayOrganise,
  retry: false,
});

const factionOptions = computed(() => (factions.value ?? []).map((faction) => ({
  value: String(faction.id),
  label: faction.name,
})));

const missing = computed(() => (event.value !== undefined && !mayOrganise.value)
  || (eventError.value instanceof ApiError && eventError.value.kind === 'not_found'));

const partyName = ref('');
const allegiance = ref('');

/**
 * One empty row per seat. Every row is the Organiser's to fill: they need not
 * be playing, and a late arrival at the desk is usually not them.
 */
const players = ref<PartyPlayer[]>([]);

watch(event, (loaded) => {
  if (loaded === undefined || players.value.length > 0) {
    return;
  }

  players.value = Array.from({ length: loaded.attendee_size }, () => ({
    name: '',
    email: '',
    factionId: '',
    armyList: '',
  }));
}, { immediate: true });

const submitting = ref(false);
const failure = ref<ApiError | null>(null);

/** A refusal no field owns — the Event itself said no — is said once, above the button. */
const problem = computed(() => {
  if (failure.value === null) {
    return null;
  }

  const blamesAField = failure.value.kind === 'validation' && Object.keys(failure.value.fields).length > 0;

  return blamesAField ? null : failure.value.message;
});

async function submit(): Promise<void> {
  submitting.value = true;
  failure.value = null;

  try {
    const added = await registerAttendee(client, props.eventSlug, {
      name: partyName.value,
      allegiance: allegiance.value === '' ? null : (allegiance.value as Allegiance),
      players: players.value.map((player) => ({
        name: player.name,
        email: player.email,
        faction_id: player.factionId === '' ? null : Number(player.factionId),
        army_list: player.armyList,
      })),
    });

    // The lists and the Event's count of places taken are behind now.
    void queryClient.invalidateQueries({ queryKey: keys.allAttendees(props.eventSlug) });
    void queryClient.invalidateQueries({ queryKey: keys.event(props.eventSlug), exact: true });

    await router.push({ name: 'attendee', params: { eventSlug: props.eventSlug, attendeeId: String(added.id) } });
  } catch (caught) {
    failure.value = caught instanceof ApiError ? caught : null;
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <main class="mx-auto flex w-full max-w-md flex-col gap-5 p-5">
    <BackLink
      :to="{ name: 'attendees', params: { eventSlug: props.eventSlug } }"
      testid="back-to-attendees"
    >
      Back to the attendees
    </BackLink>

    <MissingNotice
      v-if="missing"
      thing="page"
    />

    <p
      v-else-if="eventError"
      data-testid="add-attendee-load-error"
      role="alert"
      class="text-destructive"
    >
      {{ eventError.message }}
    </p>

    <template v-else-if="event">
      <h1 class="text-2xl font-bold tracking-tight text-foreground">
        Add attendee
      </h1>

      <!-- Full or closed does not stop an Organiser, and adding mid-Event is
           allowed: a late arrival joins from the next Round on zero. -->
      <form
        class="flex flex-col gap-6"
        novalidate
        @submit.prevent="submit"
      >
        <PartyFields
          v-model:name="partyName"
          v-model:allegiance="allegiance"
          v-model:players="players"
          voice="organiser"
          :requires-allegiance="event.requires_allegiance"
          :faction-options="factionOptions"
          :errors="failure?.fields"
        />

        <p
          v-if="problem"
          data-testid="add-attendee-problem"
          role="alert"
          class="text-sm text-destructive"
        >
          {{ problem }}
        </p>

        <AppButton
          type="submit"
          data-testid="submit-attendee"
          :disabled="submitting"
          block
        >
          {{ submitting ? 'Adding…' : 'Add attendee' }}
        </AppButton>
      </form>
    </template>

    <p
      v-else
      class="text-muted-foreground-1"
    >
      Loading the event…
    </p>
  </main>
</template>
