import type { ApiClient } from './client';

/** The Game a flag is raised against, as the queue needs to show it. */
export interface FlaggedGame {
    id: number;
    table_number: number | null;
    is_bye: boolean;
    round: { id: number; number: number; name: string | null };
    attendees: { id: number; name: string; scores: Record<string, number | string> }[];
}

export interface ResultFlag {
    id: number;
    game_id: number;
    reason: string | null;
    flagged_at: string | null;
    flagged_by: { id: number; name: string };
    game?: FlaggedGame;
    resolved_at: string | null;
}

function eventPath(slug: string): string {
    return `/api/events/${slug}`;
}

/**
 * Say the submitted result is wrong.
 *
 * There is no self-correction path — first submission wins — so this is how a
 * Player who disagrees gets the score looked at. Flagging twice is harmless:
 * the API returns the flag already open rather than raising a second one.
 */
export function flagResult(client: ApiClient, slug: string, gameId: number, reason: string): Promise<ResultFlag> {
    return client.post<{ data: ResultFlag }>(`${eventPath(slug)}/games/${gameId}/flag`, { reason })
        .then((response) => response.data);
}

/** The open flags on this Event, oldest first. Organisers only. */
export function fetchFlags(client: ApiClient, slug: string): Promise<ResultFlag[]> {
    return client.get<{ data: ResultFlag[] }>(`${eventPath(slug)}/flags`)
        .then((response) => response.data);
}

/**
 * Close the flag on a Game.
 *
 * Separate from correcting the scores on purpose: an Organiser who checks a
 * disputed result and finds it was right still has to be able to clear it.
 */
export function resolveFlag(client: ApiClient, slug: string, gameId: number): Promise<ResultFlag> {
    return client.post<{ data: ResultFlag }>(`${eventPath(slug)}/games/${gameId}/flag/resolve`, {})
        .then((response) => response.data);
}
