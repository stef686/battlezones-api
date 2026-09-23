<script setup lang="ts">
import { useQuery, keepPreviousData } from '@tanstack/vue-query';
import { computed, ref, watch } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchAttendees } from '@/api/events';
import { keys } from '@/api/keys';
import AllegianceBadge from '@/components/AllegianceBadge.vue';
import AppAvatar from '@/components/AppAvatar.vue';
import MissingNotice from '@/components/MissingNotice.vue';
import AppButton from '@/components/AppButton.vue';
import TextField from '@/components/TextField.vue';

const props = defineProps<{ eventSlug: string }>();

const client = useApiClient();

const search = ref('');
const page = ref(1);

// A new search starts at the first page: page 3 of the old results is not
// page 3 of the new ones.
watch(search, () => {
  page.value = 1;
});

const { data, isPending, error } = useQuery({
  queryKey: computed(() => keys.attendees(props.eventSlug, search.value, page.value)),
  queryFn: () => fetchAttendees(client, props.eventSlug, { search: search.value, page: page.value }),
  // The list stays on screen while the next page loads, so the hall does not
  // flash empty between pages.
  placeholderData: keepPreviousData,
  retry: false,
});

const missing = computed(() => error.value instanceof ApiError && error.value.kind === 'not_found');
const attendees = computed(() => data.value?.data ?? []);
const meta = computed(() => data.value?.meta ?? null);
const empty = computed(() => data.value !== undefined && attendees.value.length === 0);
</script>

<template>
  <main class="mx-auto flex w-full max-w-md flex-col gap-5 p-5">
    <!-- The nav names this screen, so the heading is for a screen reader
         landing here from a deep link and costs no space. -->
    <h1 class="sr-only">
      Who is here
    </h1>

    <MissingNotice
      v-if="missing"
      thing="event"
    />

    <template v-else>
      <!-- The placeholder carries what a label and a hint used to, because
           this is one field on a screen whose whole job is the list beneath
           it, and it says the same thing in one line rather than three. The
           label is still there for a screen reader, just not on screen. -->
      <TextField
        v-model="search"
        label="Search by team, player, club or faction"
        label-hidden
        type="search"
        placeholder="Search by team, player, club or faction…"
        testid="attendee-search"
      />

      <p
        v-if="isPending"
        class="text-muted-foreground-1"
      >
        Loading…
      </p>

      <p
        v-else-if="error"
        data-testid="attendees-error"
        role="alert"
        class="text-destructive"
      >
        {{ (error as ApiError).message }}
      </p>

      <p
        v-else-if="empty"
        data-testid="attendees-empty"
        class="text-muted-foreground-1"
      >
        Nobody matches that.
      </p>

      <!-- It runs to both edges rather than sitting in a card, as the
           standings table does: this list is the whole screen rather than one
           panel among several, and the width a card gives back is width the
           team names were being truncated to fit. Rules top and bottom are
           all that is left of the card. The rows carry the page's own inset
           so their names line up with the field above them. -->
      <ul
        v-else
        class="-mx-5 divide-y divide-card-divider border-y border-card-line"
      >
        <li
          v-for="attendee in attendees"
          :key="attendee.id"
        >
          <RouterLink
            :to="{ name: 'attendee', params: { eventSlug: props.eventSlug, attendeeId: attendee.id } }"
            :data-testid="`attendee-${attendee.id}`"
            class="flex items-center gap-3 px-5 py-3.5 hover:bg-muted-hover focus:bg-muted-hover focus:outline-hidden"
          >
            <AppAvatar
              :name="attendee.name"
              :src="attendee.avatar"
            />

            <span class="flex min-w-0 flex-1 flex-col">
              <span class="truncate text-base font-semibold text-foreground">{{ attendee.name }}</span>
              <span class="truncate text-sm text-muted-foreground">
                {{ attendee.members.map((member) => member.name).join(' & ') }}
              </span>
            </span>

            <AllegianceBadge :allegiance="attendee.allegiance" />
          </RouterLink>
        </li>
      </ul>

      <nav
        v-if="meta && meta.last_page > 1"
        class="flex items-center justify-between gap-3"
        aria-label="Pagination"
      >
        <AppButton
          data-testid="previous-page"
          variant="secondary"
          size="sm"
          :disabled="meta.current_page <= 1"
          @click="page = Math.max(1, page - 1)"
        >
          Previous
        </AppButton>

        <span
          data-testid="page-position"
          class="text-sm text-muted-foreground-1"
        >
          Page {{ meta.current_page }} of {{ meta.last_page }}
        </span>

        <AppButton
          data-testid="next-page"
          variant="secondary"
          size="sm"
          :disabled="meta.current_page >= meta.last_page"
          @click="page = page + 1"
        >
          Next
        </AppButton>
      </nav>
    </template>
  </main>
</template>
