<script setup lang="ts">
/**
 * Every Event the viewer runs, from the account drawer's Organiser > Events.
 *
 * The only way back to a Draft: the public listing leaves Drafts out, and an
 * Organiser who leaves the create wizard has no other road to theirs. One
 * plain list in start order, each row leading to the organise hub rather than
 * the Player-facing Event, because that is what somebody arriving from here
 * has come to do.
 *
 * Split into Happening now / Upcoming / Past by the Events' dates (see
 * `whenTabs`). An empty tab stays in the strip, greyed, so the layout never
 * shifts; the list opens on the first tab with anything in it. The open tab
 * is kept in `?tab=` with a replace, so Back from an Event lands on the tab
 * it was opened from, and an empty or unknown `?tab=` falls back to the
 * default rather than opening on nothing.
 *
 * Only Draft and Cancelled wear a badge. They are the two an Organiser has to
 * be told about — nobody else can see them — and badging every status would
 * bury those two among words that need no action.
 */
import { useQuery } from '@tanstack/vue-query';
import { ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';

import { useApiClient } from '@/api';
import { type EventStatus, fetchOrganisedEvents, type OrganisedEvent } from '@/api/events';
import { keys } from '@/api/keys';
import AppButton from '@/components/AppButton.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import TabStrip from '@/components/TabStrip.vue';
import { formatDateRange } from '@/lib/dates';
import { whenTabs } from '@/lib/organised-events';

const client = useApiClient();
const route = useRoute();
const router = useRouter();

const { data, isPending, isError } = useQuery({
  queryKey: keys.organisedEvents(),
  queryFn: () => fetchOrganisedEvents(client),
  // Creating, publishing and editing an Event all happen elsewhere, often
  // seconds before coming back here, and none of them knows this list.
  refetchOnMount: 'always',
});

const events = computed(() => data.value ?? []);

const tabs = computed(() => whenTabs(events.value).map((tab) => ({ ...tab, disabled: tab.events.length === 0 })));

/** The first tab with anything in it, left to right; -1, so nothing opens, when every tab is empty. */
const defaultTab = computed(() => tabs.value.findIndex((tab) => !tab.disabled));

const selectedTab = computed<number>({
  get() {
    const named = tabs.value.findIndex((tab) => tab.id === route.query.tab);

    return named !== -1 && !tabs.value[named]?.disabled ? named : defaultTab.value;
  },
  set(index) {
    const tab = tabs.value[index];

    if (tab !== undefined) {
      void router.replace({ query: { ...route.query, tab: tab.id } });
    }
  },
});

const BADGES: Partial<Record<EventStatus, string>> = {
  draft: 'Draft',
  cancelled: 'Cancelled',
};

function dates(event: OrganisedEvent): string {
  return formatDateRange(event.starts_at, event.ends_at, event.timezone);
}
</script>

<template>
  <main class="mx-auto flex w-full max-w-md flex-col gap-5 p-5">
    <h1 class="text-2xl font-bold tracking-tight text-foreground">
      Events you organise
    </h1>

    <p
      v-if="isPending"
      class="text-muted-foreground-1"
    >
      Loading your events…
    </p>

    <p
      v-else-if="isError"
      data-testid="organising-problem"
      role="alert"
      class="text-destructive"
    >
      Your events could not be loaded. Try again in a moment.
    </p>

    <template v-else>
      <TabStrip
        v-model="selectedTab"
        :items="tabs"
        label="When"
        id-prefix="organising"
      >
        <template #default="{ item }">
          <ul class="-mx-5 divide-y divide-card-divider">
            <li
              v-for="event in item.events"
              :key="event.id"
            >
              <RouterLink
                :to="{ name: 'organise', params: { eventSlug: event.slug } }"
                data-testid="organised-event"
                class="flex items-center gap-3 px-5 py-4 hover:bg-muted-hover focus:bg-muted-hover focus:outline-hidden"
              >
                <span class="flex min-w-0 flex-1 flex-col gap-0.5">
                  <span class="flex items-center gap-2">
                    <span
                      data-testid="organised-event-name"
                      class="truncate text-base font-semibold text-foreground"
                    >{{ event.name }}</span>
                    <StatusBadge
                      v-if="BADGES[event.status]"
                      data-testid="organised-event-status"
                    >
                      {{ BADGES[event.status] }}
                    </StatusBadge>
                  </span>
                  <span
                    data-testid="organised-event-dates"
                    class="text-sm text-muted-foreground-1"
                  >{{ dates(event) }}</span>
                  <span
                    v-if="event.venue.city"
                    class="text-sm text-muted-foreground"
                  >{{ event.venue.city }}</span>
                </span>
                <ChevronRight class="size-4 shrink-0 text-muted-foreground" />
              </RouterLink>
            </li>
          </ul>
        </template>
      </TabStrip>

      <div
        v-if="events.length === 0"
        data-testid="organising-empty"
        class="flex flex-col items-start gap-3"
      >
        <p class="text-muted-foreground-1">
          You're not organising any events yet.
        </p>
        <AppButton
          :to="{ name: 'create-event' }"
          data-testid="organising-create"
        >
          Run an event
        </AppButton>
      </div>
    </template>
  </main>
</template>
