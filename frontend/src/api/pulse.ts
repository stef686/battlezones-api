import type { ApiClient } from './client';

/**
 * What has moved at an Event, and nothing else.
 *
 * The stamps are opaque strings: the client compares them, it never reads a
 * date out of them. That keeps the contract to "different means changed".
 */
export interface Pulse {
    current_round: { id: number; number: number } | null;
    rounds: string | null;
    standings: string | null;
    polls: string | null;
}

export type StampName = 'rounds' | 'standings' | 'polls';

export function fetchPulse(client: ApiClient, slug: string): Promise<Pulse> {
    return client.get<{ data: Pulse }>(`/api/events/${encodeURIComponent(slug)}/pulse`)
        .then((response) => response.data);
}

/**
 * Which stamps changed between two readings.
 *
 * The first reading moves nothing: everything is new then, and treating it as
 * a change would refetch every expensive resource the moment a screen opens —
 * which is exactly what it had just loaded.
 *
 * A change of current Round counts as the Rounds having moved even if the
 * stamp somehow has not, because that is the fact Players are waiting on.
 */
export function movedStamps(previous: Pulse | null, next: Pulse): StampName[] {
    if (previous === null) {
        return [];
    }

    const moved: StampName[] = [];

    if (previous.rounds !== next.rounds || previous.current_round?.id !== next.current_round?.id) {
        moved.push('rounds');
    }

    if (previous.standings !== next.standings) {
        moved.push('standings');
    }

    if (previous.polls !== next.polls) {
        moved.push('polls');
    }

    return moved;
}
