import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, toValue, watch, type MaybeRefOrGetter } from 'vue';
import { useRouter } from 'vue-router';

import { useApiClient } from '@/api';
import { fetchAttendee, fetchEvent, type AttendeeMember } from '@/api/events';
import { keys } from '@/api/keys';
import { fetchPolls } from '@/api/polls';
import { useSessionStore } from '@/stores/session';

/**
 * Everything the My team screens are about: the Event, the viewer's own party,
 * and which member of it the viewer is.
 *
 * The hub and its five sub-screens all read the same two resources, so they
 * read them from here rather than each rebuilding the same pair of queries and
 * the same "am I even entered" redirect.
 */
export function useMyTeam(eventSlug: MaybeRefOrGetter<string>) {
    const client = useApiClient();
    const session = useSessionStore();
    const router = useRouter();
    const queryClient = useQueryClient();

    const slug = computed(() => toValue(eventSlug));

    const { data: event, isPending: eventPending } = useQuery({
        queryKey: computed(() => keys.event(slug.value)),
        queryFn: () => fetchEvent(client, slug.value),
        retry: false,
    });

    const attendeeId = computed(() => event.value?.viewer?.attendee_id ?? null);

    const { data: attendee, isPending: attendeePending } = useQuery({
        queryKey: computed(() => keys.attendee(slug.value, attendeeId.value as number)),
        queryFn: () => fetchAttendee(client, slug.value, attendeeId.value as number),
        enabled: computed(() => attendeeId.value !== null),
        retry: false,
    });

    const { data: polls } = useQuery({
        queryKey: computed(() => keys.polls(slug.value)),
        queryFn: () => fetchPolls(client, slug.value),
        retry: false,
    });

    /** Not entered yet: the thing to offer is the entry form, not an empty team. */
    watch(event, (loaded) => {
        if (loaded !== undefined && loaded.viewer?.is_attendee !== true) {
            void router.replace({ name: 'register', params: { eventSlug: slug.value } });
        }
    }, { immediate: true });

    const me = computed<AttendeeMember | null>(
        () => attendee.value?.members.find((member) => member.id === session.viewer?.id) ?? null,
    );

    /**
     * The other seat. Doubles is the only party size this app runs, so there
     * is at most one, and it is empty until somebody is enrolled into it.
     */
    const partner = computed<AttendeeMember | null>(
        () => attendee.value?.members.find((member) => member.id !== session.viewer?.id) ?? null,
    );

    const isDoubles = computed(() => (event.value?.attendee_size ?? 1) > 1);

    const paintingPoll = computed(() => (polls.value ?? []).find((poll) => poll.type === 'painting') ?? null);

    const loading = computed(() => eventPending.value || attendeePending.value);

    /** The party changed, and every screen reading it is now behind. */
    function refresh(): Promise<void> {
        return queryClient.invalidateQueries({ queryKey: ['events', slug.value, 'attendees'] });
    }

    return { event, attendee, attendeeId, me, partner, isDoubles, paintingPoll, loading, refresh };
}
