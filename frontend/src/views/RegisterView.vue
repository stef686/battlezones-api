<script setup lang="ts">
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, reactive, ref, watch } from 'vue';
import { useRouter } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchEvent, fetchFactions, registerAttendee, type Allegiance, type PlayerEntry } from '@/api/events';
import SelectField from '@/components/SelectField.vue';
import TextField from '@/components/TextField.vue';
import { useSessionStore } from '@/stores/session';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();
const session = useSessionStore();
const router = useRouter();
const queryClient = useQueryClient();

const { data: event, isPending, error } = useQuery({
  queryKey: ['event', props.eventSlug],
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

const { data: factions } = useQuery({
  queryKey: ['factions', props.eventSlug],
  queryFn: () => fetchFactions(client, props.eventSlug),
  retry: false,
});

const factionOptions = computed(() => (factions.value ?? []).map((faction) => ({
  value: String(faction.id),
  label: faction.name,
})));

const ALLEGIANCES: { value: Allegiance; label: string }[] = [
  { value: 'loyalist', label: 'Loyalist' },
  { value: 'traitor', label: 'Traitor' },
];

const partyName = ref('');
const allegiance = ref('');

/**
 * One row per Player, the first of which is the Captain filling this in. Their
 * address is not editable: registering a party is entering it, and the API
 * refuses a registration its registrant is not part of.
 */
const players = reactive<{ name: string; email: string; factionId: string }[]>([]);

watch([event, () => session.viewer], () => {
  if (event.value === undefined || session.viewer === null || players.length > 0) {
    return;
  }

  players.push({ name: '', email: session.viewer.email, factionId: '' });

  for (let index = 1; index < event.value.attendee_size; index += 1) {
    players.push({ name: '', email: '', factionId: '' });
  }
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

function fieldErrors(field: string): string[] {
  return failure.value?.fields[field] ?? [];
}

async function submit(): Promise<void> {
  submitting.value = true;
  problem.value = null;
  failure.value = null;

  const entries: PlayerEntry[] = players.map((player) => ({
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
    await queryClient.invalidateQueries({ queryKey: ['event', props.eventSlug] });

    await router.replace({ name: 'my-team', params: { eventSlug: props.eventSlug } });
  } catch (caught) {
    failure.value = caught instanceof ApiError ? caught : null;

    // A 409 here means the last place went while this form was open, which is
    // a different thing from anything the form got wrong.
    problem.value = failure.value === null || failure.value.kind === 'validation'
      ? null
      : failure.value.message;
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <main class="mx-auto flex min-h-screen w-full max-w-md flex-col gap-6 p-5">
    <p
      v-if="isPending"
      class="text-ink-muted"
    >
      Loading the event…
    </p>

    <p
      v-else-if="error"
      data-testid="register-load-error"
      class="text-danger"
    >
      {{ (error as ApiError).message }}
    </p>

    <template v-else-if="event">
      <header class="flex flex-col gap-1">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">
          Enter {{ event.name }}
        </h1>
        <p
          v-if="placesTaken"
          data-testid="places-taken"
          class="text-sm text-ink-muted"
        >
          {{ placesTaken }}
        </p>
      </header>

      <p
        v-if="full"
        data-testid="event-full"
        class="rounded-2xl bg-surface-raised p-5 text-ink-muted"
      >
        This event is full. Ask an organiser whether there is a waiting list.
      </p>

      <p
        v-else-if="!mayRegister"
        data-testid="registration-closed"
        class="rounded-2xl bg-surface-raised p-5 text-ink-muted"
      >
        Entries are not open to you for this event.
      </p>

      <form
        v-else
        class="flex flex-col gap-6"
        novalidate
        @submit.prevent="submit"
      >
        <section class="flex flex-col gap-4">
          <TextField
            v-model="partyName"
            label="Team name"
            testid="party-name"
            hint="Optional. Left blank, your names are used."
            :errors="fieldErrors('name')"
          />

          <SelectField
            v-if="event.requires_allegiance"
            v-model="allegiance"
            label="Allegiance"
            placeholder="Choose a side"
            testid="allegiance"
            :options="ALLEGIANCES"
            hint="This event pairs Loyalist against Traitor."
            :errors="fieldErrors('allegiance')"
          />
        </section>

        <section
          v-for="(player, index) in players"
          :key="index"
          class="flex flex-col gap-4 rounded-2xl bg-surface-raised p-5"
          :data-testid="`player-${index}`"
        >
          <h2 class="text-sm uppercase tracking-widest text-ink-faint">
            {{ index === 0 ? 'You' : `Player ${index + 1}` }}
          </h2>

          <template v-if="index === 0">
            <p
              data-testid="my-email"
              class="text-ink"
            >
              {{ player.email }}
            </p>
          </template>

          <template v-else>
            <TextField
              v-model="player.name"
              label="Their name"
              :testid="`player-${index}-name`"
              :errors="fieldErrors(`players.${index}.name`)"
            />

            <TextField
              v-model="player.email"
              label="Their email"
              type="email"
              inputmode="email"
              :testid="`player-${index}-email`"
              hint="They get their own invitation — they do not share yours."
              :errors="fieldErrors(`players.${index}.email`)"
            />
          </template>

          <SelectField
            v-model="player.factionId"
            label="Faction"
            placeholder="Not decided yet"
            :testid="`player-${index}-faction`"
            :options="factionOptions"
            :errors="fieldErrors(`players.${index}.faction_id`)"
          />
        </section>

        <p
          v-for="message in fieldErrors('players')"
          :key="message"
          data-testid="players-error"
          class="text-sm text-danger"
        >
          {{ message }}
        </p>

        <p
          v-if="problem"
          data-testid="register-problem"
          class="text-sm text-danger"
        >
          {{ problem }}
        </p>

        <button
          type="submit"
          data-testid="submit-registration"
          :disabled="submitting"
          class="rounded-lg bg-accent px-4 py-3 font-semibold text-accent-ink disabled:opacity-60"
        >
          {{ submitting ? 'Entering…' : 'Enter the event' }}
        </button>
      </form>
    </template>
  </main>
</template>
