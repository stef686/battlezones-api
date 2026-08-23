import type { ApiClient } from './client';

export interface RoundSummary {
    id: number;
    number: number;
    name: string | null;
    status: string;
}

export interface PairedAttendee {
    id: number;
    name: string;
    members: { id: number; name: string; faction: { id: number; name: string } | null }[];
    scores: Record<string, number | string>;
}

export interface Pairing {
    id: number;
    table_number: number | null;
    is_bye: boolean;
    is_rematch: boolean;
    attendees: PairedAttendee[];
}

export interface RoundDetail extends RoundSummary {
    games: Pairing[];
}

/**
 * The Rounds this reader may see.
 *
 * Draft Rounds are filtered by the API, not here: a Player is never sent one,
 * so there is nothing for the screen to hide. An Organiser is sent them, and
 * they arrive marked as drafts.
 */
export function fetchRounds(client: ApiClient, slug: string): Promise<RoundSummary[]> {
    return client.get<{ data: RoundSummary[] }>(`${eventPath(slug)}/rounds`).then((response) => response.data);
}

export function fetchRound(client: ApiClient, slug: string, roundId: number): Promise<RoundDetail> {
    return client.get<{ data: RoundDetail }>(`${eventPath(slug)}/rounds/${roundId}`).then((response) => response.data);
}

function eventPath(slug: string): string {
    return `/api/events/${encodeURIComponent(slug)}`;
}
