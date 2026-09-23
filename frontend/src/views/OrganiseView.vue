<script setup lang="ts">
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, ref } from 'vue';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchEvent } from '@/api/events';
import { fetchFlags } from '@/api/flags';
import { keys } from '@/api/keys';
import { fetchRound, fetchRounds, type Pairing } from '@/api/rounds';
import { correctGameResult } from '@/api/results';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
import AppLinkRow from '@/components/AppLinkRow.vue';
import MissingNotice from '@/components/MissingNotice.vue';
import { useEventPulse } from '@/composables/useEventPulse';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();
const queryClient = useQueryClient();

const { data: event, isPending: eventPending } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

/**
 * A reader without the permission is told the page is not there.
 *
 * The same answer as an Event that does not exist, for the same reason: the
 * existence of an Organiser screen for someone else's Event is not this
 * reader's business.
 */
const mayOrganise = computed(() => event.value?.viewer?.permissions.organise === true);
const forbidden = computed(() => event.value !== undefined && !mayOrganise.value);

useEventPulse(() => props.eventSlug, computed(() => event.value?.status === 'active'));

const { data: rounds } = useQuery({
  queryKey: computed(() => keys.rounds(props.eventSlug)),
  queryFn: () => fetchRounds(client, props.eventSlug),
  enabled: mayOrganise,
  retry: false,
});

/**
 * Open disputes are counted here so the queue is somewhere an Organiser is
 * sent, rather than somewhere they have to remember to go and look.
 */
const { data: flags } = useQuery({
  queryKey: computed(() => keys.flags(props.eventSlug)),
  queryFn: () => fetchFlags(client, props.eventSlug),
  enabled: mayOrganise,
  retry: false,
});

const disputed = computed(() => (flags.value ?? []).length);

/**
 * The shape of the Event, said in the row that leads to it: the Game System it
 * is played under and how many Players make up a party.
 */
const formatState = computed(() => {
  const loaded = event.value;

  if (loaded === undefined) {
    return '';
  }

  const system = loaded.game_system?.name ?? 'No game system';

  return `${system} · ${loaded.attendee_size > 1 ? `teams of ${loaded.attendee_size}` : 'singles'}`;
});

const live = computed(() => [...(rounds.value ?? [])]
  .filter((round) => round.status === 'live')
  .sort((left, right) => right.number - left.number)[0] ?? null);

/**
 * The Round being played is read in full so the row that leads to it can say
 * how many tables are still out, and so its Byes can be scored here.
 */
const { data: liveDetail } = useQuery({
  queryKey: computed(() => keys.round(props.eventSlug, live.value?.id ?? 0)),
  queryFn: () => fetchRound(client, props.eventSlug, live.value!.id),
  enabled: computed(() => mayOrganise.value && live.value !== null),
  retry: false,
});

const outstanding = computed(() => (liveDetail.value?.games ?? [])
  .filter((game) => !game.is_bye && game.result.submitted_at === null)
  .sort(byTable));

/**
 * Where the Round being played has got to, said in the row that leads to it:
 * how many tables are still out, or that they have all reported.
 */
const roundState = computed(() => {
  if (live.value === null) {
    return 'Nothing being played';
  }

  if (outstanding.value.length === 0) {
    return `${title(live.value)} · all reported`;
  }

  return `${title(live.value)} · ${outstanding.value.length} to go`;
});

const problem = ref<string | null>(null);

function byTable(left: Pairing, right: Pairing): number {
  if (left.is_bye !== right.is_bye) {
    return left.is_bye ? 1 : -1;
  }

  return (left.table_number ?? 0) - (right.table_number ?? 0);
}

function title(round: { number: number; name: string | null }): string {
  return round.name ?? `Round ${round.number}`;
}

/** Victory points for a Bye, which has no opponent to agree them with. */
const byeScores = ref<Record<number, string>>({});
const scoringBye = ref<number | null>(null);

/**
 * The Byes whose points have been written.
 *
 * Entering a Bye's points changes nothing else on the screen — the win was
 * counted when the Round was paired — so without saying so the save leaves no
 * trace, and an Organiser walks away not knowing whether it took.
 */
const savedByes = ref<number[]>([]);

async function scoreBye(pairing: Pairing): Promise<void> {
  const attendee = pairing.attendees[0];

  if (attendee === undefined) {
    return;
  }

  scoringBye.value = pairing.id;
  problem.value = null;
  savedByes.value = savedByes.value.filter((id) => id !== pairing.id);

  try {
    await correctGameResult(client, props.eventSlug, pairing.id, {
      [attendee.id]: { 'victory-points': Number(byeScores.value[pairing.id] ?? 0) },
    });

    await queryClient.invalidateQueries({ queryKey: keys.rounds(props.eventSlug) });
    await queryClient.invalidateQueries({ queryKey: keys.standings(props.eventSlug) });
    savedByes.value = [...savedByes.value, pairing.id];
  } catch (caught) {
    problem.value = caught instanceof ApiError ? caught.message : 'Those points could not be saved.';
  } finally {
    scoringBye.value = null;
  }
}

/** Byes in the Round being played, which are what an Organiser has to score. */
const byes = computed(() => (liveDetail.value?.games ?? []).filter((game) => game.is_bye));
</script>

<template>
  <main class="mx-auto flex w-full max-w-md flex-col gap-5 p-5">
    <p
      v-if="eventPending"
      class="text-muted-foreground-1"
    >
      Loading…
    </p>

    <MissingNotice
      v-else-if="forbidden || event === undefined"
      thing="page"
    />

    <template v-else>
      <h1 class="sr-only">
        Run the event
      </h1>

      <!-- Settings are touched once before an Event and pairings every ten
           minutes during it, so they are linked from here rather than run
           from here. Edge to edge as a list group, the way My team reads: a
           way through to other screens, not panels beside the running of the
           Round below them. -->
      <ul class="-mx-5 -mt-5 divide-y divide-card-divider border-b border-t border-card-line">
        <AppLinkRow
          :to="{ name: 'event-settings', params: { eventSlug: props.eventSlug } }"
          label="Event settings"
          value="Name, dates, venue"
          testid="settings-link"
        />

        <!-- What the Event *is*, as against what it is called: the shape a
             Player enters at, and the columns they are scored on. -->
        <AppLinkRow
          :to="{ name: 'event-format', params: { eventSlug: props.eventSlug } }"
          label="Event format"
          :value="formatState"
          testid="format-link"
        />

        <!-- What is holding up the next Round is the question an Organiser
             is standing there asking, so the row says it rather than making
             them open the Round to find out. -->
        <AppLinkRow
          :to="{ name: 'rounds', params: { eventSlug: props.eventSlug } }"
          label="Rounds"
          :value="roundState"
          :outstanding="outstanding.length > 0"
          testid="rounds-link"
        />

        <AppLinkRow
          :to="{ name: 'flags', params: { eventSlug: props.eventSlug } }"
          label="Disputed results"
          :value="disputed > 0 ? `${disputed} waiting` : 'None'"
          :outstanding="disputed > 0"
          testid="flags-link"
        />
      </ul>

      <!-- A Bye has nobody to agree a result with, so its Victory Points are
           entered here. The win itself was awarded when the Round was paired. -->
      <section
        v-for="bye in byes"
        :key="bye.id"
        :data-testid="`bye-${bye.id}`"
        class="flex flex-col gap-3 rounded-xl border border-card-line bg-card p-5 shadow-2xs"
      >
        <h2 class="text-xs font-medium uppercase tracking-widest text-muted-foreground">
          Bye · {{ bye.attendees[0]?.name }}
        </h2>
        <p class="text-sm text-muted-foreground-1">
          Counted as a win already. Enter the victory points they are credited with.
        </p>

        <div class="flex items-center gap-3">
          <input
            v-model="byeScores[bye.id]"
            type="number"
            inputmode="numeric"
            :data-testid="`bye-score-${bye.id}`"
            class="w-24 shrink-0 rounded-lg border border-border bg-background-2 px-3 py-2.5 text-right text-lg tabular-nums text-foreground focus:border-primary focus:ring-1 focus:ring-primary focus:outline-hidden"
          >
          <AppButton
            :data-testid="`save-bye-${bye.id}`"
            :disabled="scoringBye === bye.id"
            class="flex-1"
            @click="scoreBye(bye)"
          >
            {{ scoringBye === bye.id ? 'Saving…' : 'Save points' }}
          </AppButton>
        </div>

        <p
          v-if="savedByes.includes(bye.id)"
          :data-testid="`bye-saved-${bye.id}`"
          role="status"
          class="text-sm text-success"
        >
          Saved. They are counted in the standings.
        </p>
      </section>

      <AppAlert
        v-if="problem"
        data-testid="organise-problem"
        tone="error"
      >
        {{ problem }}
      </AppAlert>
    </template>
  </main>
</template>
