<script setup lang="ts">
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, ref, watch } from 'vue';
import { useRouter } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchEvent, fetchFactions, registerAttendee, type Allegiance, type PlayerEntry } from '@/api/events';
import { keys } from '@/api/keys';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import PartyFields, { type PartyPlayer } from '@/components/PartyFields.vue';
import { useSessionStore } from '@/stores/session';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();
const session = useSessionStore();
const router = useRouter();
const queryClient = useQueryClient();

const { data: event, isPending, error } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

const { data: factions } = useQuery({
  queryKey: computed(() => keys.factions(props.eventSlug)),
  queryFn: () => fetchFactions(client, props.eventSlug),
  retry: false,
});

const factionOptions = computed(() => (factions.value ?? []).map((faction) => ({
  value: String(faction.id),
  label: faction.name,
})));

const partyName = ref('');
const allegiance = ref('');

/**
 * One row per Player, the first of which is the Captain filling this in. Their
 * address is not editable: registering a party is entering it, and the API
 * refuses a registration its registrant is not part of.
 */
const players = ref<PartyPlayer[]>([]);

watch([event, () => session.viewer], () => {
  if (event.value === undefined || session.viewer === null || players.value.length > 0) {
    return;
  }

  players.value = Array.from({ length: event.value.attendee_size }, (_, index) => ({
    name: '',
    email: index === 0 ? session.viewer?.email ?? '' : '',
    factionId: '',
    armyList: '',
  }));
}, { immediate: true });

/** Already entered: there is nothing to register, so show them their team. */
watch(event, (loaded) => {
  if (loaded?.viewer?.is_attendee === true) {
    void router.replace({ name: 'my-team', params: { eventSlug: props.eventSlug } });
  }
}, { immediate: true });

const full = computed(() => event.value?.is_full === true);
const mayRegister = computed(() => event.value?.viewer?.permissions.register === true);
const placesTaken = computed(() => {
  const loaded = event.value;

  if (loaded === undefined || loaded.max_attendees === null || loaded.attendees_count === undefined) {
    return null;
  }

  return `${loaded.attendees_count} of ${loaded.max_attendees} places taken`;
});

const submitting = ref(false);
const problem = ref<string | null>(null);
const failure = ref<ApiError | null>(null);

async function submit(): Promise<void> {
  submitting.value = true;
  problem.value = null;
  failure.value = null;

  const entries: PlayerEntry[] = players.value.map((player) => ({
    name: player.name,
    email: player.email,
    faction_id: player.factionId === '' ? null : Number(player.factionId),
  }));

  try {
    await registerAttendee(client, props.eventSlug, {
      name: partyName.value,
      allegiance: allegiance.value === '' ? null : (allegiance.value as Allegiance),
      players: entries,
    });

    // The Event carries the viewer's own state, and it now says something
    // different: without this the team screen reads a cached "not entered"
    // and sends the Captain straight back to this form.
    await queryClient.invalidateQueries({ queryKey: keys.event(props.eventSlug) });

    await router.replace({ name: 'my-team', params: { eventSlug: props.eventSlug } });
  } catch (caught) {
    failure.value = caught instanceof ApiError ? caught : null;

    // A 409 here means the last place went while this form was open, and a
    // 422 naming no field means the Event itself refused — a Draft, which
    // only its Organisers can reach this form for. Neither is anything the
    // form got wrong, so neither belongs against a field.
    const blamesAField = failure.value?.kind === 'validation' && Object.keys(failure.value.fields).length > 0;

    problem.value = failure.value === null || blamesAField ? null : failure.value.message;
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <main class="mx-auto flex w-full max-w-md flex-col gap-6 p-5">
    <p
      v-if="isPending"
      class="text-muted-foreground-1"
    >
      Loading the event…
    </p>

    <p
      v-else-if="error"
      data-testid="register-load-error"
      role="alert"
      class="text-destructive"
    >
      {{ (error as ApiError).message }}
    </p>

    <template v-else-if="event">
      <header>
        <h1 class="text-2xl font-bold tracking-tight text-foreground">
          Enter {{ event.name }}
        </h1>
        <p
          v-if="placesTaken"
          data-testid="places-taken"
          class="mt-1 text-sm text-muted-foreground-1"
        >
          {{ placesTaken }}
        </p>
      </header>

      <AppAlert
        v-if="full"
        data-testid="event-full"
      >
        This event is full. Ask an organiser whether there is a waiting list.
      </AppAlert>

      <AppAlert
        v-else-if="!mayRegister"
        data-testid="registration-closed"
      >
        Entries are not open to you for this event.
      </AppAlert>

      <form
        v-else
        class="flex flex-col gap-6"
        novalidate
        @submit.prevent="submit"
      >
        <PartyFields
          v-model:name="partyName"
          v-model:allegiance="allegiance"
          v-model:players="players"
          voice="player"
          :requires-allegiance="event.requires_allegiance"
          :faction-options="factionOptions"
          :errors="failure?.fields"
        />

        <p
          v-if="problem"
          data-testid="register-problem"
          role="alert"
          class="text-sm text-destructive"
        >
          {{ problem }}
        </p>

        <AppButton
          type="submit"
          data-testid="submit-registration"
          :disabled="submitting"
          block
        >
          {{ submitting ? 'Entering…' : 'Enter the event' }}
        </AppButton>
      </form>
    </template>
  </main>
</template>
