<script setup lang="ts">
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, ref, watch } from 'vue';
import { RouterLink, useRouter } from 'vue-router';

import { useApiClient } from '@/api';
import { submitArmyList } from '@/api/army-lists';
import { amendAttendee, fetchAttendee, fetchEvent, fetchFactions, recordMyFaction, type Allegiance } from '@/api/events';
import { ApiError } from '@/api/errors';
import { keys } from '@/api/keys';
import { enterPainting, fetchPolls } from '@/api/polls';
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
/**
 * The painting Poll, if this Event runs one.
 *
 * Entering is the Player saying their army is on the display table, which is
 * theirs to say — the number beside it is not, and belongs to whoever laid
 * the table out.
 */
const { data: polls } = useQuery({
  queryKey: computed(() => keys.polls(props.eventSlug)),
  queryFn: () => fetchPolls(client, props.eventSlug),
  retry: false,
});

const paintingPoll = computed(() => (polls.value ?? []).find((poll) => poll.type === 'painting') ?? null);
const paintingEntered = computed(() => attendee.value?.painting_entered === true);
const enteringPainting = ref(false);

async function togglePainting(): Promise<void> {
  if (attendeeId.value === null) {
    return;
  }

  enteringPainting.value = true;

  try {
    await enterPainting(client, props.eventSlug, attendeeId.value, !paintingEntered.value);
    await queryClient.invalidateQueries({ queryKey: ['events', props.eventSlug, 'attendees'] });
  } finally {
    enteringPainting.value = false;
  }
}

const armyList = ref('');
const submittingList = ref(false);
const listProblem = ref<string | null>(null);

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
  armyList.value = me.value?.army_list ?? '';
});

/**
 * Locked means submitted: the API refuses an edit and only an Organiser can
 * reopen it, so the screen has to say which of the two states a Player is in
 * rather than leaving them to guess from an empty box.
 */
const listLocked = computed(() => me.value?.army_list_locked === true);

async function submitList(): Promise<void> {
  submittingList.value = true;
  listProblem.value = null;

  try {
    await submitArmyList(client, props.eventSlug, armyList.value);
    await queryClient.invalidateQueries({ queryKey: ['events', props.eventSlug, 'attendees'] });
  } catch (caught) {
    listProblem.value = caught instanceof ApiError ? caught.message : 'That could not be sent.';
  } finally {
    submittingList.value = false;
  }
}

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
          <span class="flex flex-col items-end gap-0.5 text-sm">
            <span class="text-ink-muted">{{ mate.faction?.name ?? 'Faction not chosen' }}</span>
            <span :class="mate.army_list_locked ? 'text-success' : 'text-ink-faint'">
              {{ mate.army_list_locked ? 'List in' : 'List not submitted' }}
            </span>
          </span>
        </div>

        <p class="text-sm text-ink-faint">
          They were sent their own invitation. They choose their own faction.
        </p>
      </section>

      <section
        v-if="paintingPoll"
        data-testid="painting-entry"
        class="flex flex-col gap-3 rounded-2xl bg-surface-raised p-5"
      >
        <h2 class="text-sm uppercase tracking-widest text-ink-faint">
          {{ paintingPoll.name }}
        </h2>

        <p
          v-if="paintingEntered"
          data-testid="painting-entered"
          class="text-success"
        >
          Your army is on the display table{{ attendee.display_number ? ` under number ${attendee.display_number}` : '' }}.
        </p>
        <p
          v-else
          class="text-sm text-ink-muted"
        >
          Put your army on the display table and enter it here, so people can vote for it.
        </p>

        <button
          type="button"
          data-testid="enter-painting"
          :disabled="enteringPainting"
          class="self-start rounded-lg border border-border px-3 py-2 text-sm text-ink disabled:opacity-60"
          @click="togglePainting"
        >
          {{ paintingEntered ? 'Take it out of the vote' : 'Enter my army' }}
        </button>
      </section>

      <section
        data-testid="army-list-form"
        class="flex flex-col gap-3 rounded-2xl bg-surface-raised p-5"
      >
        <h2 class="text-sm uppercase tracking-widest text-ink-faint">
          Your army list
        </h2>

        <template v-if="listLocked">
          <p
            data-testid="army-list-locked"
            class="text-success"
          >
            Submitted and locked. Ask an organiser to reopen it if it needs correcting.
          </p>
          <p
            data-testid="army-list-mine"
            class="whitespace-pre-wrap text-sm text-ink"
          >
            {{ me?.army_list }}
          </p>
        </template>

        <template v-else>
          <textarea
            v-model="armyList"
            data-testid="army-list"
            rows="8"
            placeholder="Detachments, units, wargear…"
            class="rounded-lg border border-border bg-surface-sunken px-3 py-2.5 text-ink outline-none focus:border-accent"
          />
          <p class="text-sm text-ink-muted">
            Submitting locks the list. Only an organiser can reopen it.
          </p>
          <button
            type="button"
            data-testid="submit-army-list"
            :disabled="submittingList"
            class="rounded-xl bg-accent px-4 py-3 font-semibold text-accent-ink disabled:opacity-60"
            @click="submitList"
          >
            {{ submittingList ? 'Sending…' : 'Submit list' }}
          </button>
        </template>

        <p
          v-if="listProblem"
          data-testid="army-list-problem"
          class="text-sm text-danger"
        >
          {{ listProblem }}
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
