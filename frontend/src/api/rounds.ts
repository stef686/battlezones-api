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
    /**
     * Absent for anyone who does not run the Event: whether a Game repeats a
     * pairing is what an Organiser checks before publishing a Draft, and the
     * API omits the key rather than sending a false. Presence is the
     * permission — the screen does not re-check who is looking.
     */
    is_rematch?: boolean;
    result: { submitted_at: string | null; is_flagged: boolean };
    attendees: PairedAttendee[];
}

export interface ScoreColumn {
    slug: string;
    name: string;
}

export interface RoundDetail extends RoundSummary {
    /**
     * What every Game in the Round is scored on, sent whether or not any
     * result has landed. A Game nobody has played yet still shows the columns
     * it is waiting on rather than collapsing to nothing.
     */
    score_types: ScoreColumn[];
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

/**
 * The Round a reader arriving at the Rounds tab wants: the last one that has
 * been published.
 *
 * Drafts are skipped rather than led with. Only an Organiser is ever sent one,
 * and landing them on pairings nobody else can see yet — while the Round being
 * played sits one chevron behind — reads as the Event having moved on when it
 * has not. An Organiser with nothing but a Draft still gets it, because it is
 * the only Round there is.
 */
export function latestPlayable(rounds: RoundSummary[]): RoundSummary | null {
    const ordered = byNumber(rounds);
    const published = ordered.filter((round) => round.status !== 'draft');

    return published.at(-1) ?? ordered.at(-1) ?? null;
}

/** Rounds in the order they are played, whatever order they arrived in. */
export function byNumber(rounds: RoundSummary[]): RoundSummary[] {
    return [...rounds].sort((left, right) => left.number - right.number);
}

/**
 * A score column's heading, short enough to sit over a number.
 *
 * The initials of the Score Type's words — Match Points becomes MP, Victory
 * Points VP — which is what the Standings table has always called them and
 * what a Player says out loud. Derived rather than listed, because an Event
 * declares its own Score Types and a hard-coded pair only ever fits one Event.
 * The full name travels with it, so nothing depends on reading the initials.
 */
export function columnLabel(column: ScoreColumn): string {
    const initials = column.name
        .split(/\s+/)
        .filter((word) => word.length > 0)
        .map((word) => word[0])
        .join('')
        .toUpperCase();

    return initials.slice(0, 3) || column.name.slice(0, 3).toUpperCase();
}

/** What a Round is called, falling back to its number rather than to blank. */
export function roundTitle(round: Pick<RoundSummary, 'number' | 'name'>): string {
    return round.name ?? `Round ${round.number}`;
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

/**
 * Recombine two Games of a Draft Round.
 *
 * The exchange itself is not a choice — the API performs the only legal one —
 * so this sends the two Games and nothing else.
 */
export function swapPairings(client: ApiClient, slug: string, roundId: number, gameIds: [number, number]): Promise<RoundDetail> {
    return client.post<{ data: RoundDetail }>(`${eventPath(slug)}/rounds/${roundId}/swap`, { game_ids: gameIds })
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
