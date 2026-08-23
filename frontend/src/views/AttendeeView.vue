<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query';
import { computed } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchAttendee } from '@/api/events';
import AllegianceBadge from '@/components/AllegianceBadge.vue';
import MissingNotice from '@/components/MissingNotice.vue';

const props = defineProps<{ eventSlug: string; attendeeId: string }>();

const client = useApiClient();

const { data: attendee, isPending, error } = useQuery({
  queryKey: computed(() => ['attendee', props.eventSlug, Number(props.attendeeId)]),
  queryFn: () => fetchAttendee(client, props.eventSlug, Number(props.attendeeId)),
  retry: false,
});

// A team in an Event nobody may see, a team that never existed, and a team in
// another Event all answer the same way here.
const missing = computed(() => error.value instanceof ApiError && error.value.kind === 'not_found');
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
        </article>
      </section>
    </template>
  </main>
</template>
