<script setup lang="ts">
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, ref } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchEvent } from '@/api/events';
import { keys } from '@/api/keys';
import { closePoll, fetchPolls, fetchResults, openPoll, type Poll, type PollResults } from '@/api/polls';
import AppButton from '@/components/AppButton.vue';
import { useEventPulse } from '@/composables/useEventPulse';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();
const queryClient = useQueryClient();

const { data: event } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

// A Poll opening is the thing Players are waiting on here, and the pulse is
// how a screen left open finds out.
useEventPulse(() => props.eventSlug, computed(() => event.value?.status === 'active'));

const { data: polls, isPending, error } = useQuery({
  queryKey: computed(() => keys.polls(props.eventSlug)),
  queryFn: () => fetchPolls(client, props.eventSlug),
  retry: false,
});

const list = computed(() => polls.value ?? []);

/**
 * Open, open-but-not-to-you, and closed are three different answers.
 *
 * A favourite-opponent Poll opens per team as each finishes its last Game, so
 * "the poll is open" and "you can vote" are not the same fact, and a Player
 * told the wrong one goes looking for a button that is not there.
 */
function state(poll: Poll): string {
  if (poll.is_open_for_me === true) {
    return 'Open to you';
  }

  if (poll.is_open) {
    return 'Not yet open to you';
  }

  return poll.closes_at === null ? 'Not open' : 'Closed';
}

function mayVote(poll: Poll): boolean {
  return poll.is_open_for_me === true;
}

const mayOrganise = computed(() => event.value?.viewer?.permissions.organise === true);

const working = ref<number | null>(null);
const problem = ref<string | null>(null);

/** Tallies fetched on request, since reading them is a deliberate act. */
const results = ref<Record<number, PollResults>>({});

async function toggleOpen(poll: Poll): Promise<void> {
  working.value = poll.id;
  problem.value = null;

  try {
    await (poll.is_open ? closePoll : openPoll)(client, props.eventSlug, poll.id);
    await queryClient.invalidateQueries({ queryKey: keys.polls(props.eventSlug) });
  } catch (caught) {
    problem.value = caught instanceof ApiError ? caught.message : 'That could not be done.';
  } finally {
    working.value = null;
  }
}

/**
 * A tally is only ever read after voting is over.
 *
 * The API refuses nothing here — Organisers may read them whenever — but
 * knowing who is winning while people are still voting is not a thing anyone
 * at the Event should be able to say, so the screen does not offer it.
 */
async function showResults(poll: Poll): Promise<void> {
  working.value = poll.id;
  problem.value = null;

  try {
    results.value = { ...results.value, [poll.id]: await fetchResults(client, props.eventSlug, poll.id) };
  } catch (caught) {
    problem.value = caught instanceof ApiError ? caught.message : 'Those could not be read.';
  } finally {
    working.value = null;
  }
}

/** Over, rather than merely not open: a Poll nobody has opened has no tally. */
function isOver(poll: Poll): boolean {
  return !poll.is_open && poll.closes_at !== null;
}
</script>

<template>
  <main class="mx-auto flex w-full max-w-md flex-col gap-5 p-5">
    <h1 class="text-2xl font-bold tracking-tight text-foreground">
      Votes
    </h1>

    <p
      v-if="isPending"
      class="text-muted-foreground-1"
    >
      Loading the votes…
    </p>

    <p
      v-else-if="error"
      data-testid="polls-error"
      role="alert"
      class="text-destructive"
    >
      {{ (error as ApiError).message }}
    </p>

    <p
      v-else-if="list.length === 0"
      data-testid="polls-empty"
      class="text-muted-foreground-1"
    >
      This event is not running any votes.
    </p>

    <ul
      v-else
      class="flex flex-col gap-3"
    >
      <li
        v-for="entry in list"
        :key="entry.id"
        class="overflow-hidden rounded-xl border border-card-line bg-card shadow-2xs"
      >
        <!-- A Poll nobody may vote in is still worth reading, so the row is
             only a link when there is somewhere for it to go. -->
        <component
          :is="mayVote(entry) ? RouterLink : 'div'"
          v-bind="mayVote(entry) ? { to: { name: 'poll', params: { eventSlug: props.eventSlug, pollId: entry.id } } } : {}"
          :data-testid="`poll-${entry.id}`"
          class="flex items-center justify-between gap-3 px-4 py-3.5"
          :class="mayVote(entry) ? 'hover:bg-muted-hover focus:bg-muted-hover focus:outline-hidden' : ''"
        >
          <span class="flex min-w-0 flex-col gap-0.5">
            <span class="truncate text-base font-semibold text-foreground">{{ entry.name }}</span>
            <span class="text-sm text-muted-foreground">{{ entry.votes_per_player }} pick{{ entry.votes_per_player === 1 ? '' : 's' }}</span>
          </span>
          <span
            class="shrink-0 text-sm font-medium"
            :class="mayVote(entry) ? 'text-primary' : 'text-muted-foreground'"
          >{{ state(entry) }}</span>
        </component>

        <div
          v-if="mayOrganise"
          class="flex flex-col gap-2 border-t border-card-divider bg-card-footer px-4 py-3"
        >
          <AppButton
            v-if="!entry.is_open"
            :data-testid="`open-poll-${entry.id}`"
            variant="secondary"
            size="sm"
            :disabled="working === entry.id"
            class="self-start"
            @click="toggleOpen(entry)"
          >
            Open voting
          </AppButton>
          <AppButton
            v-else
            :data-testid="`close-poll-${entry.id}`"
            variant="secondary"
            size="sm"
            :disabled="working === entry.id"
            class="self-start"
            @click="toggleOpen(entry)"
          >
            Close voting
          </AppButton>

          <AppButton
            v-if="isOver(entry) && results[entry.id] === undefined"
            :data-testid="`show-results-${entry.id}`"
            variant="secondary"
            size="sm"
            :disabled="working === entry.id"
            class="self-start"
            @click="showResults(entry)"
          >
            Read the tallies
          </AppButton>

          <!-- Ties come back unresolved, and are left that way: which of two
               equal armies wins is a judgement made in the room. -->
          <ol
            v-if="results[entry.id]"
            :data-testid="`results-${entry.id}`"
            class="flex flex-col gap-1 rounded-lg bg-background-2 p-4"
          >
            <li
              v-for="tally in results[entry.id]?.tallies ?? []"
              :key="tally.attendee.id"
              class="flex items-baseline justify-between gap-3 text-sm"
            >
              <span class="min-w-0 truncate text-foreground">
                {{ tally.attendee.display_number ? `${tally.attendee.display_number}. ` : '' }}{{ tally.attendee.name }}
              </span>
              <span class="tabular-nums text-muted-foreground-1">{{ tally.votes }}</span>
            </li>
            <li
              v-if="(results[entry.id]?.tallies ?? []).length === 0"
              class="text-sm text-muted-foreground"
            >
              Nobody voted.
            </li>
          </ol>
        </div>
      </li>
    </ul>

    <p
      v-if="problem"
      data-testid="polls-problem"
      role="alert"
      class="text-destructive"
    >
      {{ problem }}
    </p>
  </main>
</template>
