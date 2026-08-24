import type { ApiClient } from './client';
import type { AttendeeSummary } from './events';

export type PollType = 'painting' | 'favourite_opponent';

export interface Poll {
    id: number;
    name: string;
    type: PollType;
    votes_per_player: number;
    opens_at: string | null;
    closes_at: string | null;
    /** Whether the window is open at all. */
    is_open: boolean;
    /**
     * Whether it is open to this reader, which is a different question: a
     * favourite-opponent Poll opens per team, as each finishes its last Game.
     */
    is_open_for_me: boolean | null;
    /** This reader's own picks. Revising starts from what they last sent. */
    my_ballot: number[];
}

export interface Tally {
    attendee: { id: number; name: string; display_number: number | null };
    votes: number;
}

export interface PollResults {
    poll: { id: number; name: string; type: PollType; is_open: boolean };
    tallies: Tally[];
}

function eventPath(slug: string): string {
    return `/api/events/${slug}`;
}

export function fetchPolls(client: ApiClient, slug: string): Promise<Poll[]> {
    return client.get<{ data: Poll[] }>(`${eventPath(slug)}/polls`).then((response) => response.data);
}

/**
 * The Attendees this reader may pick: armies on the display table, or the
 * teams they actually played. Their own team is never among them.
 */
export function fetchCandidates(client: ApiClient, slug: string, pollId: number): Promise<AttendeeSummary[]> {
    return client.get<{ data: AttendeeSummary[] }>(`${eventPath(slug)}/polls/${pollId}/candidates`)
        .then((response) => response.data);
}

/**
 * Send the whole Ballot, never one vote.
 *
 * The API replaces it wholesale, which is what makes "change my mind about my
 * second pick" the same call as "vote" — and keeps the per-Player limit from
 * being a check-then-write race. An empty array clears it.
 */
export function replaceBallot(client: ApiClient, slug: string, pollId: number, attendeeIds: number[]): Promise<number[]> {
    return client.put<{ data: { attendee_ids: number[] } }>(`${eventPath(slug)}/polls/${pollId}/ballot`, { attendee_ids: attendeeIds })
        .then((response) => response.data.attendee_ids);
}

export function openPoll(client: ApiClient, slug: string, pollId: number): Promise<Poll> {
    return client.post<{ data: Poll }>(`${eventPath(slug)}/polls/${pollId}/open`, {}).then((response) => response.data);
}

export function closePoll(client: ApiClient, slug: string, pollId: number): Promise<Poll> {
    return client.post<{ data: Poll }>(`${eventPath(slug)}/polls/${pollId}/close`, {}).then((response) => response.data);
}

/** Organisers only, permanently: winners are announced in the room. */
export function fetchResults(client: ApiClient, slug: string, pollId: number): Promise<PollResults> {
    return client.get<{ data: PollResults }>(`${eventPath(slug)}/polls/${pollId}/results`)
        .then((response) => response.data);
}

/** Say this army is on the display table, so it can be voted for. */
export function enterPainting(client: ApiClient, slug: string, attendeeId: number, entered: boolean): Promise<unknown> {
    return client.patch(`${eventPath(slug)}/attendees/${attendeeId}/painting`, { painting_entered: entered });
}
