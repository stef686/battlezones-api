<script setup lang="ts">
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, ref } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import { revealArmyLists, unlockArmyList } from '@/api/army-lists';
import { ApiError } from '@/api/errors';
import { fetchAttendee, fetchEvent } from '@/api/events';
import { keys } from '@/api/keys';
import AllegianceBadge from '@/components/AllegianceBadge.vue';
import MissingNotice from '@/components/MissingNotice.vue';

const props = defineProps<{ eventSlug: string; attendeeId: string }>();

const client = useApiClient();
const queryClient = useQueryClient();

const { data: event } = useQuery({
  queryKey: computed(() => keys.event(props.eventSlug)),
  queryFn: () => fetchEvent(client, props.eventSlug),
  retry: false,
});

const mayOrganise = computed(() => event.value?.viewer?.permissions.organise === true);

const { data: attendee, isPending, error } = useQuery({
  queryKey: computed(() => keys.attendee(props.eventSlug, Number(props.attendeeId))),
  queryFn: () => fetchAttendee(client, props.eventSlug, Number(props.attendeeId)),
  retry: false,
});

// A team in an Event nobody may see, a team that never existed, and a team in
// another Event all answer the same way here.
const missing = computed(() => error.value instanceof ApiError && error.value.kind === 'not_found');

/**
 * Whether this reader is entitled to the lists at all.
 *
 * The API omits `army_list` rather than nulling it when a team's lists are
 * closed, so an absent key and an empty list mean different things: one is
 * not yet the reader's business, the other is what the Player submitted.
 */
const listsRevealed = computed(() => (attendee.value?.members ?? [])
  .some((member) => member.army_list !== undefined));

const working = ref(false);
const problem = ref<string | null>(null);

/** Free a team whose lists are held up by a Player who never submitted. */
async function reveal(): Promise<void> {
  await run(() => revealArmyLists(client, props.eventSlug, Number(props.attendeeId)));
}

/** Reopen one Player's list, which is the only way back out of locked. */
async function unlock(memberId: number): Promise<void> {
  await run(() => unlockArmyList(client, props.eventSlug, Number(props.attendeeId), memberId));
}

async function run(action: () => Promise<unknown>): Promise<void> {
  working.value = true;
  problem.value = null;

  try {
    await action();
    await queryClient.invalidateQueries({ queryKey: keys.attendee(props.eventSlug, Number(props.attendeeId)) });
  } catch (caught) {
    problem.value = caught instanceof ApiError ? caught.message : 'That could not be done.';
  } finally {
    working.value = false;
  }
}
</script>

<template>
  <main class="mx-auto flex min-h-screen w-full max-w-md flex-col gap-6 p-5">
    <RouterLink
      :to="{ name: 'attendees', params: { eventSlug: props.eventSlug } }"
      data-testid="back-to-attendees"
      class="text-sm text-ink-muted underline underline-offset-4"
    >
      Back to who is here
    </RouterLink>

    <p
      v-if="isPending"
      class="text-ink-muted"
    >
      Loading the team…
    </p>

    <MissingNotice
      v-else-if="missing"
      thing="team"
    />

    <p
      v-else-if="error"
      data-testid="attendee-error"
      class="text-danger"
    >
      {{ (error as ApiError).message }}
    </p>

    <template v-else-if="attendee">
      <header class="flex flex-col items-start gap-3">
        <h1
          data-testid="attendee-name"
          class="text-2xl font-semibold tracking-tight text-ink"
        >
          {{ attendee.name }}
        </h1>
        <AllegianceBadge :allegiance="attendee.allegiance" />
      </header>

      <section class="flex flex-col gap-3">
        <h2 class="text-sm uppercase tracking-widest text-ink-faint">
          {{ attendee.members.length === 1 ? 'Player' : 'Players' }}
        </h2>

        <article
          v-for="member in attendee.members"
          :key="member.id"
          :data-testid="`member-${member.id}`"
          class="flex flex-col gap-0.5 rounded-2xl bg-surface-raised px-4 py-3.5"
        >
          <p class="text-lg text-ink">
            {{ member.name }}
          </p>
          <p
            data-testid="member-faction"
            class="text-sm"
            :class="member.faction ? 'text-ink-muted' : 'text-ink-faint'"
          >
            {{ member.faction?.name ?? 'Faction not chosen' }}
          </p>

          <p
            v-if="!listsRevealed"
            class="text-sm"
            :class="member.army_list_locked ? 'text-success' : 'text-ink-faint'"
          >
            {{ member.army_list_locked ? 'List in' : 'List not submitted' }}
          </p>

          <p
            v-else
            :data-testid="`army-list-${member.id}`"
            class="mt-2 whitespace-pre-wrap text-sm"
            :class="member.army_list ? 'text-ink' : 'text-ink-faint'"
          >
            {{ member.army_list || 'No list was written.' }}
          </p>

          <!-- Locking has no other way out, and a wrong list matters to every
               opponent who prepares against it. -->
          <button
            v-if="mayOrganise && member.army_list_locked"
            type="button"
            :data-testid="`unlock-${member.id}`"
            :disabled="working"
            class="mt-2 self-start rounded-lg border border-border px-3 py-2 text-sm text-ink disabled:opacity-60"
            @click="unlock(member.id)"
          >
            Reopen this list
          </button>
        </article>

        <!-- Closed lists are said to be closed. A blank where a list would be
             reads as a Player who never wrote one. -->
        <p
          v-if="!listsRevealed"
          data-testid="lists-not-revealed"
          class="text-sm text-ink-faint"
        >
          Army lists have not been revealed. They open once every player on the team has submitted.
        </p>

        <button
          v-if="mayOrganise && !listsRevealed"
          type="button"
          data-testid="reveal-army-lists"
          :disabled="working"
          class="self-start rounded-lg border border-border px-3 py-2 text-sm text-ink disabled:opacity-60"
          @click="reveal"
        >
          Reveal these lists anyway
        </button>

        <p
          v-if="problem"
          data-testid="attendee-problem"
          class="text-sm text-danger"
        >
          {{ problem }}
        </p>
      </section>
    </template>
  </main>
</template>
