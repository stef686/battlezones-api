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
    allegiance: string | null;
    members: { id: number; name: string; faction: { id: number; name: string } | null }[];
    scores: Record<string, number | string>;
}

export interface Pairing {
    id: number;
    table_number: number | null;
    is_bye: boolean;
    is_rematch: boolean;
    result: { submitted_at: string | null; is_flagged: boolean };
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

/**
 * Pair the field into a new Draft Round.
 *
 * Refused, with a message naming what to put right, while the current Round
 * is unpublished or still has results outstanding.
 */
export function generateRound(client: ApiClient, slug: string): Promise<RoundDetail> {
    return client.post<{ data: RoundDetail }>(`${eventPath(slug)}/rounds`).then((response) => response.data);
}

export function publishRound(client: ApiClient, slug: string, roundId: number): Promise<RoundDetail> {
    return client.post<{ data: RoundDetail }>(`${eventPath(slug)}/rounds/${roundId}/publish`)
        .then((response) => response.data);
}

/** Refused once any result is in: by then the Round has been played on. */
export function unpublishRound(client: ApiClient, slug: string, roundId: number): Promise<RoundDetail> {
    return client.delete<{ data: RoundDetail }>(`${eventPath(slug)}/rounds/${roundId}/publish`)
        .then((response) => response.data);
}

function eventPath(slug: string): string {
    return `/api/events/${encodeURIComponent(slug)}`;
}
