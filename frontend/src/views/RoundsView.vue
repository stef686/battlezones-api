<script setup lang="ts">
/**
 * The Rounds tab, which is not a list of Rounds.
 *
 * A Player taps Rounds to see the pairings being played right now — the list
 * was a menu standing between them and the only Round most readers ever want.
 * So this resolves which Round that is and hands over to the Round screen,
 * where the chevrons reach the rest.
 *
 * It replaces rather than pushes: the list is not a place to go back to, and
 * leaving it in the history would make the phone's back button bounce off it.
 */
import { useQuery } from '@tanstack/vue-query';
import { computed, watch } from 'vue';
import { useRouter } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { keys } from '@/api/keys';
import { fetchRounds, latestPlayable, type RoundSummary } from '@/api/rounds';
import MissingNotice from '@/components/MissingNotice.vue';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();
const router = useRouter();

const { data: rounds, isPending, error } = useQuery({
  queryKey: computed(() => keys.rounds(props.eventSlug)),
  queryFn: () => fetchRounds(client, props.eventSlug),
  retry: false,
});

const missing = computed(() => error.value instanceof ApiError && error.value.kind === 'not_found');
const empty = computed(() => rounds.value !== undefined && rounds.value.length === 0);

const destination = computed<RoundSummary | null>(() => latestPlayable(rounds.value ?? []));

watch(destination, (round) => {
  if (round === null) {
    return;
  }

  void router.replace({ name: 'round', params: { eventSlug: props.eventSlug, roundId: round.id } });
}, { immediate: true });
</script>

<template>
  <main class="mx-auto flex w-full max-w-md flex-col gap-5 p-5">
    <!-- The nav names this screen, so the heading is for a screen reader
         landing here from a deep link and costs no space. -->
    <h1 class="sr-only">
      Rounds
    </h1>

    <MissingNotice
      v-if="missing"
      thing="event"
    />

    <p
      v-else-if="error"
      data-testid="rounds-error"
      role="alert"
      class="text-destructive"
    >
      {{ (error as ApiError).message }}
    </p>

    <p
      v-else-if="empty"
      data-testid="rounds-empty"
      class="text-muted-foreground-1"
    >
      No rounds yet. Pairings appear here the moment the first round is published.
    </p>

    <!-- Still showing while the redirect lands, which is why it is last: a
         resolved Round leaves this screen before the message is read. -->
    <p
      v-else-if="isPending || destination"
      class="text-muted-foreground-1"
    >
      Loading the rounds…
    </p>
  </main>
</template>
