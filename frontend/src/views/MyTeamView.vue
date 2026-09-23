<script setup lang="ts">
import { computed } from 'vue';

import AllegianceBadge from '@/components/AllegianceBadge.vue';
import AppLinkRow from '@/components/AppLinkRow.vue';
import AppAvatar from '@/components/AppAvatar.vue';
import { useMyTeam } from '@/composables/useMyTeam';

const props = defineProps<{ eventSlug: string }>();

const { event, attendee, me, partner, isDoubles, paintingPoll, loading } = useMyTeam(() => props.eventSlug);

/**
 * The hub says where every part of an entry stands so a Player can see what is
 * outstanding without opening five screens to find out. Each row edits one
 * thing, because a phone in a hall is a bad place to scroll past four forms to
 * reach the fifth.
 */
const teamName = computed(() => attendee.value?.name ?? 'Not named');
const myFaction = computed(() => me.value?.faction?.name ?? 'Not chosen');
const myList = computed(() => (me.value?.army_list_locked === true ? 'Submitted' : 'Not submitted'));

const partnerState = computed(() => {
  const mate = partner.value;

  if (mate === null) {
    return 'Nobody yet';
  }

  return mate.invite_outstanding === true ? `${mate.name} · waiting` : mate.name;
});

const paintingState = computed(() => (attendee.value?.painting_entered === true ? 'Entered' : 'Not entered'));
</script>

<template>
  <main class="mx-auto flex w-full max-w-md flex-col gap-6 px-5 pb-5">
    <p
      v-if="loading"
      class="pt-5 text-muted-foreground-1"
    >
      Loading your team…
    </p>

    <template v-else-if="attendee && event">
      <!-- The team as the rest of the Event sees it, at the top of the screen
           that edits it: the badge on the left with the name and the side it
           fights for beside it, exactly as the team's own page draws them. -->
      <header class="flex items-center gap-4 pt-5">
        <AppAvatar
          :name="attendee.name ?? ''"
          :src="attendee.avatar"
          size="lg"
        />

        <div class="flex min-w-0 flex-col items-start gap-2">
          <h1
            data-testid="team-name"
            class="text-2xl font-bold tracking-tight text-foreground"
          >
            {{ attendee.name }}
          </h1>
          <AllegianceBadge
            v-if="event.requires_allegiance"
            :allegiance="attendee.allegiance"
          />
        </div>
      </header>

      <!-- Edge to edge, and nothing but a rule between rows: the hub is a way
           through to five screens, so it should read as a list of them rather
           than as five panels competing with the Event nav above it. -->
      <ul class="-mx-5 divide-y divide-card-divider border-t border-card-line">
        <AppLinkRow
          :to="{ name: 'my-team-details', params: { eventSlug: props.eventSlug } }"
          label="Team details"
          :value="teamName"
          :outstanding="attendee.name === null"
          testid="team-details-row"
        />

        <AppLinkRow
          :to="{ name: 'my-team-faction', params: { eventSlug: props.eventSlug } }"
          label="My details"
          :value="myFaction"
          :outstanding="!me?.faction"
          testid="my-details-row"
        />

        <AppLinkRow
          :to="{ name: 'my-team-list', params: { eventSlug: props.eventSlug } }"
          label="My list"
          :value="myList"
          :outstanding="me?.army_list_locked !== true"
          testid="my-list-row"
        />

        <AppLinkRow
          v-if="isDoubles"
          :to="{ name: 'my-team-partner', params: { eventSlug: props.eventSlug } }"
          label="Partner"
          :value="partnerState"
          :outstanding="partner === null || partner.invite_outstanding === true"
          testid="partner-row"
        />

        <AppLinkRow
          v-if="paintingPoll"
          :to="{ name: 'my-team-painting', params: { eventSlug: props.eventSlug } }"
          :label="paintingPoll.name"
          :value="paintingState"
          :outstanding="attendee.painting_entered !== true"
          testid="painting-row"
        />
      </ul>
    </template>
  </main>
</template>
