import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, onScopeDispose, ref, toValue, watch, type MaybeRefOrGetter } from 'vue';

import { useApiClient } from '@/api';
import { ApiError } from '@/api/errors';
import { keys } from '@/api/keys';
import { fetchPulse, movedStamps, type Pulse } from '@/api/pulse';
import { BASE_INTERVAL_MS, nextBackoff, pollInterval } from '@/lib/polling';

/**
 * Keeps an Event's screens current while it is being run.
 *
 * One cheap poll stands in for polling everything: the pulse says what moved,
 * and only the resource that moved is retired from the cache. A Player's
 * screen therefore updates when the next Round goes Live without them
 * refreshing, without the Standings being recomputed for every Player every
 * fifteen seconds.
 */
export function useEventPulse(eventSlug: MaybeRefOrGetter<string>, inProgress: MaybeRefOrGetter<boolean>) {
    const client = useApiClient();
    const queryClient = useQueryClient();

    const visible = useDocumentVisible();
    const backoff = ref(BASE_INTERVAL_MS);
    const previous = ref<Pulse | null>(null);

    const running = computed(() => toValue(inProgress) && visible.value);

    const query = useQuery({
        queryKey: computed(() => keys.pulse(toValue(eventSlug))),
        queryFn: () => fetchPulse(client, toValue(eventSlug)),
        enabled: computed(() => toValue(inProgress)),
        // Recomputed on every tick, so a rate limit lengthens the gap without
        // the query having to be torn down and rebuilt.
        refetchInterval: () => pollInterval({
            inProgress: toValue(inProgress),
            visible: visible.value,
            backoffMs: backoff.value,
        }),
        // The poll exists to notice change, so a cached answer is no answer.
        staleTime: 0,
        // A failed poll is not worth a retry: the next tick is the retry.
        retry: false,
    });

    watch(query.data, (pulse) => {
        if (pulse === undefined) {
            return;
        }

        for (const stamp of movedStamps(previous.value, pulse)) {
            retire(stamp);
        }

        previous.value = pulse;
    });

    watch(query.error, (error) => {
        backoff.value = nextBackoff(backoff.value, error instanceof ApiError ? error : null);
    });

    watch(query.isSuccess, (succeeded) => {
        if (succeeded) {
            backoff.value = BASE_INTERVAL_MS;
        }
    });

    function retire(stamp: 'rounds' | 'standings' | 'polls'): void {
        const slug = toValue(eventSlug);

        if (stamp === 'rounds') {
            void queryClient.invalidateQueries({ queryKey: keys.rounds(slug) });
            // A published Round is how a Player gets a Game at all, and the
            // Event's own viewer block moves with it.
            void queryClient.invalidateQueries({ queryKey: keys.myGame(slug) });
            void queryClient.invalidateQueries({ queryKey: keys.schedule(slug) });

            return;
        }

        if (stamp === 'standings') {
            void queryClient.invalidateQueries({ queryKey: keys.standings(slug) });
            // A result landing changes the Game it landed on.
            void queryClient.invalidateQueries({ queryKey: keys.myGame(slug) });

            return;
        }

        void queryClient.invalidateQueries({ queryKey: ['events', slug, 'polls'] });
    }

    return {
        pulse: query.data,
        currentRound: computed(() => query.data.value?.current_round ?? null),
        isPolling: running,
        intervalMs: computed(() => backoff.value),
    };
}

/**
 * Whether the app is on screen.
 *
 * Read from the document rather than from TanStack's focus manager because
 * this drives a deliberate policy — stop entirely when hidden — and a policy
 * worth stating is worth being able to read in one place.
 */
function useDocumentVisible() {
    const visible = ref(typeof document === 'undefined' || document.visibilityState === 'visible');

    if (typeof document === 'undefined') {
        return visible;
    }

    const update = (): void => {
        visible.value = document.visibilityState === 'visible';
    };

    document.addEventListener('visibilitychange', update);
    onScopeDispose(() => document.removeEventListener('visibilitychange', update));

    return visible;
}
