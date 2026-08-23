import { afterEach, describe, expect, it, vi } from 'vitest';

import { ApiClient } from '@/api/client';
import { isOwnSubmission, submitResult, type Game } from '@/api/results';
import { InMemoryTokenStorage } from '@/api/token-storage';

const SENT = { 9: { 'victory-points': 85 }, 11: { 'victory-points': 70 } };

function game(submittedById: number | null): Game {
    return {
        id: 18,
        table_number: 7,
        is_bye: false,
        round: { id: 4, number: 1, name: 'Round 1' },
        result: {
            submitted_at: '2026-09-12T14:05:00+00:00',
            submitted_by: submittedById === null ? null : { id: submittedById, name: 'Ada Lovelace' },
            edited_at: null,
            is_flagged: false,
        },
        attendees: [
            { id: 9, name: 'Ada and partner', members: [], scores: { 'victory-points': 85, 'match-points': 3 } },
            { id: 11, name: 'Grace and partner', members: [], scores: { 'victory-points': 70, 'match-points': 0 } },
        ],
    };
}

function client(fetch: ReturnType<typeof vi.fn>) {
    vi.stubGlobal('fetch', fetch);

    const storage = new InMemoryTokenStorage();
    storage.write('a-token');

    return new ApiClient({ baseUrl: 'https://api.test', storage });
}

function ok(body: unknown) {
    return { ok: true, status: 200, headers: new Headers(), json: () => Promise.resolve(body) };
}

function conflict(body: unknown) {
    return { ok: false, status: 409, headers: new Headers(), json: () => Promise.resolve(body) };
}

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('isOwnSubmission', () => {
    it('recognises the reader’s own result', () => {
        expect(isOwnSubmission(game(12), 12, SENT)).toBe(true);
    });

    it('does not claim a result submitted by the opponent', () => {
        expect(isOwnSubmission(game(99), 12, SENT)).toBe(false);
    });

    it('does not claim a result that disagrees with what was sent', () => {
        const stored = game(12);
        stored.attendees[0]!.scores['victory-points'] = 40;

        expect(isOwnSubmission(stored, 12, SENT)).toBe(false);
    });
});

describe('submitResult', () => {
    it('reports the result when the submission lands', async () => {
        const fetch = vi.fn().mockResolvedValue(ok({ data: game(12) }));

        await expect(submitResult(client(fetch), 'open', 18, SENT, 12))
            .resolves.toMatchObject({ status: 'submitted' });

        expect(fetch).toHaveBeenCalledTimes(1);
    });

    it('retries once when the request never got an answer', async () => {
        const fetch = vi.fn()
            .mockRejectedValueOnce(new TypeError('Failed to fetch'))
            .mockResolvedValueOnce(ok({ data: game(12) }));

        await expect(submitResult(client(fetch), 'open', 18, SENT, 12))
            .resolves.toMatchObject({ status: 'submitted' });

        expect(fetch).toHaveBeenCalledTimes(2);
    });

    it('reports a lost response as success, never as a dispute', async () => {
        const fetch = vi.fn()
            .mockRejectedValueOnce(new TypeError('Failed to fetch'))
            .mockResolvedValueOnce(conflict({ message: 'A result has already been submitted.', data: game(12) }));

        const outcome = await submitResult(client(fetch), 'open', 18, SENT, 12);

        expect(outcome.status).toBe('already_submitted');
        expect(outcome.game.id).toBe(18);
    });

    it('reports a genuine conflict when the stored result is someone else’s', async () => {
        const fetch = vi.fn()
            .mockRejectedValueOnce(new TypeError('Failed to fetch'))
            .mockResolvedValueOnce(conflict({ message: 'A result has already been submitted.', data: game(99) }));

        const outcome = await submitResult(client(fetch), 'open', 18, SENT, 12);

        expect(outcome.status).toBe('conflict');
    });

    it('does not retry a request the API answered', async () => {
        const fetch = vi.fn().mockResolvedValue(conflict({ message: 'Already submitted.', data: game(99) }));

        await expect(submitResult(client(fetch), 'open', 18, SENT, 12)).rejects.toMatchObject({ kind: 'conflict' });

        expect(fetch).toHaveBeenCalledTimes(1);
    });
});
