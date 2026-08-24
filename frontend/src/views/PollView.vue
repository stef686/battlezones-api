<script setup lang="ts">
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, ref, watch } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchEvent } from '@/api/events';
import { keys } from '@/api/keys';
import { fetchCandidates, fetchPolls, replaceBallot } from '@/api/polls';
import AllegianceBadge from '@/components/AllegianceBadge.vue';
import { useEventPulse } from '@/composables/useEventPulse';

const props = defineProps<{ eventSlug: string; pollId: string }>();

const client = useApiClient();
const queryClient = useQueryClient();

const pollId = computed(() => Number(props.pollId));

const { data: event } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

// A Poll closing while somebody is still choosing is exactly the case this
// screen must not miss.
useEventPulse(() => props.eventSlug, computed(() => event.value?.status === 'active'));

const { data: polls, isPending } = useQuery({
  queryKey: computed(() => keys.polls(props.eventSlug)),
  queryFn: () => fetchPolls(client, props.eventSlug),
  retry: false,
});

const poll = computed(() => (polls.value ?? []).find((entry) => entry.id === pollId.value) ?? null);
const open = computed(() => poll.value?.is_open_for_me === true);

const { data: candidates } = useQuery({
  queryKey: computed(() => keys.pollCandidates(props.eventSlug, pollId.value)),
  queryFn: () => fetchCandidates(client, props.eventSlug, pollId.value),
  enabled: open,
  retry: false,
});

/**
 * The Ballot as it stands on this screen, in the order it was picked.
 *
 * Order is kept because dropping a pick to make room for another is the
 * common revision, and a set that reordered itself would move the thing the
 * reader is about to tap.
 */
const picks = ref<number[]>([]);

// Mirrors what the API last returned: the same Player may have voted on
// another device, and that Ballot is the one that stands.
watch(poll, (loaded) => {
  if (loaded !== null) {
    picks.value = [...loaded.my_ballot];
  }
}, { immediate: true });

const limit = computed(() => poll.value?.votes_per_player ?? 1);
const left = computed(() => Math.max(limit.value - picks.value.length, 0));

const saving = ref(false);
const saved = ref(false);
const problem = ref<string | null>(null);

function picked(attendeeId: number): boolean {
  return picks.value.includes(attendeeId);
}

/**
 * Held to the limit here rather than by the API's refusal: a Player who has
 * used their picks needs to see that, not to be told after sending.
 */
function toggle(attendeeId: number): void {
  saved.value = false;

  if (picked(attendeeId)) {
    picks.value = picks.value.filter((id) => id !== attendeeId);

    return;
  }

  if (left.value === 0) {
    return;
  }

  picks.value = [...picks.value, attendeeId];
}

async function save(): Promise<void> {
  saving.value = true;
  saved.value = false;
  problem.value = null;

  try {
    await replaceBallot(client, props.eventSlug, pollId.value, picks.value);
    await queryClient.invalidateQueries({ queryKey: keys.polls(props.eventSlug) });
    saved.value = true;
  } catch (caught) {
    problem.value = caught instanceof ApiError ? caught.message : 'That could not be sent.';
  } finally {
    saving.value = false;
  }
}
</script>

<template>
  <main class="mx-auto flex min-h-screen w-full max-w-md flex-col gap-5 p-5">
    <RouterLink
      :to="{ name: 'polls', params: { eventSlug: props.eventSlug } }"
      data-testid="back-to-polls"
      class="text-sm text-ink-muted underline underline-offset-4"
    >
      Back to the votes
    </RouterLink>

    <p
      v-if="isPending"
      class="text-ink-muted"
    >
      Loading the vote…
    </p>

    <template v-else-if="poll">
      <header class="flex flex-col gap-1">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">
          {{ poll.name }}
        </h1>
        <p
          v-if="open"
          data-testid="picks-left"
          class="text-sm text-ink-muted"
        >
          {{ left }} of {{ limit }} pick{{ limit === 1 ? '' : 's' }} left.
        </p>
      </header>

      <!-- Shut is said plainly, and with which kind of shut it is: a Poll
           that has not opened yet is not the same news as one that is over. -->
      <p
        v-if="!open"
        data-testid="poll-shut"
        class="rounded-2xl bg-surface-raised p-5 text-ink-muted"
      >
        {{ poll.closes_at ? 'Voting is closed. The winners are announced in the room.' : 'Voting is not open to you yet.' }}
      </p>

      <template v-else>
        <ul class="flex flex-col gap-3">
          <li
            v-for="candidate in candidates ?? []"
            :key="candidate.id"
          >
            <button
              type="button"
              :data-testid="`pick-${candidate.id}`"
              class="flex w-full items-center justify-between gap-3 rounded-2xl border bg-surface-raised px-4 py-3.5 text-left"
              :class="picked(candidate.id) ? 'border-accent' : 'border-transparent'"
              @click="toggle(candidate.id)"
            >
              <span class="flex min-w-0 flex-col gap-0.5">
                <span class="truncate text-lg text-ink">{{ candidate.name }}</span>
                <span class="truncate text-sm text-ink-faint">
                  {{ candidate.members.map((member) => member.faction?.name).filter(Boolean).join(' · ') }}
                </span>
              </span>
              <AllegianceBadge :allegiance="candidate.allegiance" />
            </button>
          </li>
        </ul>

        <p
          v-if="(candidates ?? []).length === 0"
          data-testid="no-candidates"
          class="text-ink-muted"
        >
          There is nobody to pick yet.
        </p>

        <button
          type="button"
          data-testid="save-ballot"
          :disabled="saving"
          class="rounded-xl bg-accent px-4 py-3 font-semibold text-accent-ink disabled:opacity-60"
          @click="save"
        >
          {{ saving ? 'Sending…' : 'Save my votes' }}
        </button>

        <p
          v-if="saved"
          data-testid="ballot-saved"
          class="text-success"
        >
          Saved. You can change them while voting is open.
        </p>

        <p
          v-if="problem"
          data-testid="ballot-problem"
          class="text-danger"
        >
          {{ problem }}
        </p>
      </template>
    </template>
  </main>
</template>
