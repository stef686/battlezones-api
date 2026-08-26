<script setup lang="ts">
import { useQuery, keepPreviousData } from '@tanstack/vue-query';
import { computed, ref, watch } from 'vue';
import { RouterLink } from 'vue-router';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { fetchAttendees } from '@/api/events';
import { keys } from '@/api/keys';
import AllegianceBadge from '@/components/AllegianceBadge.vue';
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
    <header>
      <!-- The nav names this screen, so the heading is for a screen reader
           landing here from a deep link and costs no space. -->
      <h1 class="sr-only">
        Who is here
      </h1>
      <p
        v-if="meta"
        data-testid="attendee-total"
        class="mt-1 text-sm text-muted-foreground-1"
      >
        {{ meta.total }} {{ meta.total === 1 ? 'team' : 'teams' }}
      </p>
    </header>

    <MissingNotice
      v-if="missing"
      thing="event"
    />

    <template v-else>
      <TextField
        v-model="search"
        label="Search"
        testid="attendee-search"
        hint="By team, player, club or faction."
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

      <ul
        v-else
        class="divide-y divide-card-divider overflow-hidden rounded-xl border border-card-line bg-card shadow-2xs"
      >
        <li
          v-for="attendee in attendees"
          :key="attendee.id"
        >
          <RouterLink
            :to="{ name: 'attendee', params: { eventSlug: props.eventSlug, attendeeId: attendee.id } }"
            :data-testid="`attendee-${attendee.id}`"
            class="flex items-center justify-between gap-3 px-4 py-3.5 hover:bg-muted-hover focus:bg-muted-hover focus:outline-hidden"
          >
            <span class="flex min-w-0 flex-col">
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
