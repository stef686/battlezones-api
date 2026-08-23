import type { ApiClient } from './client';
import { ApiError } from './errors';

export interface GameAttendee {
    id: number;
    name: string;
    members: { id: number; name: string; faction: { id: number; name: string } | null }[];
    scores: Record<string, number | string>;
}

export interface Game {
    id: number;
    table_number: number | null;
    is_bye: boolean;
    round: { id: number; number: number; name: string | null };
    result: {
        submitted_at: string | null;
        submitted_by?: { id: number; name: string } | null;
        edited_at: string | null;
        is_flagged: boolean;
    };
    attendees: GameAttendee[];
}

export type SubmissionOutcome =
    /** The submission landed on this attempt. */
    | { status: 'submitted'; game: Game }
    /** It had already landed — this reader's own, recognised from the conflict body. */
    | { status: 'already_submitted'; game: Game }
    /** Someone else got there first with a different result. */
    | { status: 'conflict'; game: Game; message: string };

export type Scores = Record<number, Record<string, number>>;

interface ConflictBody {
    message?: string;
    data?: Game;
}

/**
 * Whether the stored result is the one this reader just sent.
 *
 * Compared by submitter and by the scores themselves: a Player whose response
 * was lost sends the identical request again, so if both agree, their
 * submission is the one that stands and telling them to dispute it would be
 * both wrong and alarming.
 */
export function isOwnSubmission(game: Game, submitterId: number, sent: Scores): boolean {
    if (game.result.submitted_by?.id !== submitterId) {
        return false;
    }

    return Object.entries(sent).every(([attendeeId, scores]) => {
        const stored = game.attendees.find((attendee) => attendee.id === Number(attendeeId));

        if (stored === undefined) {
            return false;
        }

        return Object.entries(scores).every(([slug, value]) => Number(stored.scores[slug]) === Number(value));
    });
}

/**
 * Submit a Game result, surviving the venue wifi it will actually be used on.
 *
 * A request that gets no answer is retried once, because it may never have
 * reached the API. If it did reach it, the retry comes back 409 — and the
 * conflict body carries the stored Game, so the reader is told their result
 * is in rather than told to dispute themselves.
 */
export async function submitResult(
    client: ApiClient,
    eventSlug: string,
    gameId: number,
    scores: Scores,
    submitterId: number,
): Promise<SubmissionOutcome> {
    const path = `/api/events/${eventSlug}/games/${gameId}/result`;
    const body = { scores: scoresForRequest(scores) };

    try {
        return { status: 'submitted', game: await sendOnce(client, path, body) };
    } catch (error) {
        if (error instanceof ApiError && error.kind === 'network') {
            return reconcile(client, path, body, scores, submitterId);
        }

        throw error;
    }
}

async function reconcile(
    client: ApiClient,
    path: string,
    body: unknown,
    scores: Scores,
    submitterId: number,
): Promise<SubmissionOutcome> {
    try {
        return { status: 'submitted', game: await sendOnce(client, path, body) };
    } catch (error) {
        if (!(error instanceof ApiError) || error.kind !== 'conflict') {
            throw error;
        }

        const conflict = error.body as ConflictBody;
        const game = conflict.data;

        if (game === undefined) {
            throw error;
        }

        return isOwnSubmission(game, submitterId, scores)
            ? { status: 'already_submitted', game }
            : { status: 'conflict', game, message: error.message };
    }
}

async function sendOnce(client: ApiClient, path: string, body: unknown): Promise<Game> {
    const response = await client.post<{ data: Game }>(path, body);

    return response.data;
}

/**
 * The API keys scores by Attendee id and Score Type slug.
 */
function scoresForRequest(scores: Scores): Record<string, Record<string, number>> {
    return Object.fromEntries(Object.entries(scores).map(([attendeeId, values]) => [String(attendeeId), values]));
}
