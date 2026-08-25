<script setup lang="ts">
import { ChevronLeftIcon } from '@heroicons/vue/24/outline';
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, ref, watch } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchEvent } from '@/api/events';
import { keys } from '@/api/keys';
import { fetchCandidates, fetchPolls, replaceBallot } from '@/api/polls';
import AllegianceBadge from '@/components/AllegianceBadge.vue';
import AppAlert from '@/components/AppAlert.vue';
import AppButton from '@/components/AppButton.vue';
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
  <main class="mx-auto flex w-full max-w-md flex-col gap-5 p-5">
    <RouterLink
      :to="{ name: 'polls', params: { eventSlug: props.eventSlug } }"
      data-testid="back-to-polls"
      class="inline-flex items-center gap-x-1 self-start text-sm font-medium text-muted-foreground-1 hover:text-foreground focus:text-foreground focus:outline-hidden"
    >
      <ChevronLeftIcon
        class="size-4 shrink-0"
      />
      Back to the votes
    </RouterLink>

    <p
      v-if="isPending"
      class="text-muted-foreground-1"
    >
      Loading the vote…
    </p>

    <template v-else-if="poll">
      <header>
        <h1 class="text-2xl font-bold tracking-tight text-foreground">
          {{ poll.name }}
        </h1>
        <p
          v-if="open"
          data-testid="picks-left"
          class="mt-1 text-sm text-muted-foreground-1"
        >
          {{ left }} of {{ limit }} pick{{ limit === 1 ? '' : 's' }} left.
        </p>
      </header>

      <!-- Shut is said plainly, and with which kind of shut it is: a Poll
           that has not opened yet is not the same news as one that is over. -->
      <AppAlert
        v-if="!open"
        data-testid="poll-shut"
      >
        {{ poll.closes_at ? 'Voting is closed. The winners are announced in the room.' : 'Voting is not open to you yet.' }}
      </AppAlert>

      <template v-else>
        <ul class="flex flex-col gap-3">
          <li
            v-for="candidate in candidates ?? []"
            :key="candidate.id"
          >
            <button
              type="button"
              :data-testid="`pick-${candidate.id}`"
              :aria-pressed="picked(candidate.id)"
              class="flex w-full items-center justify-between gap-3 rounded-xl border bg-card px-4 py-3.5 text-left shadow-2xs hover:bg-muted-hover focus:bg-muted-hover focus:outline-hidden"
              :class="picked(candidate.id) ? 'border-primary' : 'border-card-line'"
              @click="toggle(candidate.id)"
            >
              <span class="flex min-w-0 flex-col gap-0.5">
                <span class="truncate text-base font-semibold text-foreground">{{ candidate.name }}</span>
                <span class="truncate text-sm text-muted-foreground">
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
          class="text-muted-foreground-1"
        >
          There is nobody to pick yet.
        </p>

        <AppButton
          data-testid="save-ballot"
          :disabled="saving"
          block
          @click="save"
        >
          {{ saving ? 'Sending…' : 'Save my votes' }}
        </AppButton>

        <AppAlert
          v-if="saved"
          data-testid="ballot-saved"
          tone="success"
        >
          Saved. You can change them while voting is open.
        </AppAlert>

        <AppAlert
          v-if="problem"
          data-testid="ballot-problem"
          tone="error"
        >
          {{ problem }}
        </AppAlert>
      </template>
    </template>
  </main>
</template>
