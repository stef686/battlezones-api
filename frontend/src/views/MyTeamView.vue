<script setup lang="ts">
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, ref, watch } from 'vue';
import { RouterLink, useRouter } from 'vue-router';

import { useApiClient } from '@/api';
import { amendAttendee, fetchAttendee, fetchEvent, fetchFactions, recordMyFaction, type Allegiance } from '@/api/events';
import { ApiError } from '@/api/errors';
import { keys } from '@/api/keys';
import SelectField from '@/components/SelectField.vue';
import TextField from '@/components/TextField.vue';
import { useSessionStore } from '@/stores/session';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();
const session = useSessionStore();
const router = useRouter();
const queryClient = useQueryClient();

const { data: event, isPending: eventPending } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

const attendeeId = computed(() => event.value?.viewer?.attendee_id ?? null);

const { data: attendee, isPending: attendeePending } = useQuery({
  queryKey: computed(() => keys.attendee(props.eventSlug, attendeeId.value as number)),
  queryFn: () => fetchAttendee(client, props.eventSlug, attendeeId.value as number),
  enabled: computed(() => attendeeId.value !== null),
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

const ALLEGIANCES: { value: Allegiance; label: string }[] = [
  { value: 'loyalist', label: 'Loyalist' },
  { value: 'traitor', label: 'Traitor' },
];

/** Not entered yet: the thing to offer is the entry form, not an empty team. */
watch(event, (loaded) => {
  if (loaded !== undefined && loaded.viewer?.is_attendee !== true) {
    void router.replace({ name: 'register', params: { eventSlug: props.eventSlug } });
  }
}, { immediate: true });

const partyName = ref('');
const allegiance = ref('');
const myFactionId = ref('');

const me = computed(() => attendee.value?.members.find((member) => member.id === session.viewer?.id) ?? null);
const teamMates = computed(() => (attendee.value?.members ?? []).filter((member) => member.id !== session.viewer?.id));

// The form mirrors what the API last returned rather than holding its own
// idea of the team: a correction made on another device wins on the next read.
watch(attendee, (loaded) => {
  if (loaded === undefined) {
    return;
  }

  partyName.value = loaded.name ?? '';
  allegiance.value = loaded.allegiance ?? '';
  myFactionId.value = me.value?.faction === null || me.value?.faction === undefined ? '' : String(me.value.faction.id);
});

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

    await recordMyFaction(client, props.eventSlug, myFactionId.value === '' ? null : Number(myFactionId.value));

    await queryClient.invalidateQueries({ queryKey: ['events', props.eventSlug, 'attendees'] });
    saved.value = true;
  } catch (caught) {
    failure.value = caught instanceof ApiError ? caught : null;
  } finally {
    saving.value = false;
  }
}
</script>

<template>
  <main class="mx-auto flex min-h-screen w-full max-w-md flex-col gap-6 p-5">
    <p
      v-if="eventPending || attendeePending"
      class="text-ink-muted"
    >
      Loading your team…
    </p>

    <template v-else-if="attendee && event">
      <header class="flex flex-col gap-1">
        <p class="text-sm uppercase tracking-widest text-ink-faint">
          {{ event.name }}
        </p>
        <h1
          data-testid="team-name"
          class="text-2xl font-semibold tracking-tight text-ink"
        >
          {{ attendee.name }}
        </h1>
      </header>

      <section
        v-if="teamMates.length > 0"
        class="flex flex-col gap-3 rounded-2xl bg-surface-raised p-5"
      >
        <h2 class="text-sm uppercase tracking-widest text-ink-faint">
          Playing with you
        </h2>

        <div
          v-for="mate in teamMates"
          :key="mate.id"
          :data-testid="`team-mate-${mate.id}`"
          class="flex items-baseline justify-between gap-3"
        >
          <span class="text-ink">{{ mate.name }}</span>
          <span class="text-sm text-ink-muted">{{ mate.faction?.name ?? 'Faction not chosen' }}</span>
        </div>

        <p class="text-sm text-ink-faint">
          They were sent their own invitation. They choose their own faction.
        </p>
      </section>

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
          hint="Frozen once a round goes live."
          :errors="fieldErrors('allegiance')"
        />

        <SelectField
          v-model="myFactionId"
          label="Your faction"
          placeholder="Not decided yet"
          testid="my-faction"
          :options="factionOptions"
          :errors="fieldErrors('faction_id')"
        />

        <p
          v-if="failure && failure.kind !== 'validation'"
          data-testid="team-error"
          class="text-sm text-danger"
        >
          {{ failure.message }}
        </p>

        <p
          v-if="saved"
          data-testid="team-saved"
          class="text-sm text-success"
        >
          Saved.
        </p>

        <button
          type="submit"
          data-testid="save-team"
          :disabled="saving"
          class="mt-2 rounded-lg bg-accent px-4 py-3 font-semibold text-accent-ink disabled:opacity-60"
        >
          {{ saving ? 'Saving…' : 'Save' }}
        </button>
      </form>

      <RouterLink
        :to="{ name: 'my-game', params: { eventSlug: props.eventSlug } }"
        data-testid="my-game-link"
        class="text-center text-ink-muted underline underline-offset-4"
      >
        See your game
      </RouterLink>
    </template>
  </main>
</template>
