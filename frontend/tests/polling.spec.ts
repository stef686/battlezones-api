import { describe, expect, it } from 'vitest';

import { ApiError } from '@/api/errors';
import { movedStamps, type Pulse } from '@/api/pulse';
import { BASE_INTERVAL_MS, MAX_INTERVAL_MS, nextBackoff, pollInterval } from '@/lib/polling';

function pulse(overrides: Partial<Pulse> = {}): Pulse {
    return {
        current_round: { id: 4, number: 2 },
        rounds: '2026-09-12T13:30:00Z',
        standings: '2026-09-12T14:05:12Z',
        polls: null,
        ...overrides,
    };
}

function rateLimited(retryAfter: number | null): ApiError {
    return new ApiError({
        kind: 'rate_limited',
        status: 429,
        message: 'Too Many Attempts.',
        fields: {},
        retryAfter,
        body: null,
    });
}

describe('when to poll', () => {
    it('polls while the event is being run and the app is on screen', () => {
        expect(pollInterval({ inProgress: true, visible: true, backoffMs: BASE_INTERVAL_MS }))
            .toBe(BASE_INTERVAL_MS);
    });

    it('stops entirely when the event is not in progress', () => {
        // Nothing moves before an Event starts or after it finishes, so
        // polling it costs the venue's wifi and the phone's day for nothing.
        expect(pollInterval({ inProgress: false, visible: true, backoffMs: BASE_INTERVAL_MS })).toBe(false);
    });

    it('stops while the app is hidden', () => {
        expect(pollInterval({ inProgress: true, visible: false, backoffMs: BASE_INTERVAL_MS })).toBe(false);
    });

    it('honours a backoff longer than the base interval', () => {
        expect(pollInterval({ inProgress: true, visible: true, backoffMs: 60_000 })).toBe(60_000);
    });

    it('never polls faster than the base interval, whatever it is handed', () => {
        expect(pollInterval({ inProgress: true, visible: true, backoffMs: 10 })).toBe(BASE_INTERVAL_MS);
    });
});

describe('backing off', () => {
    it('doubles the interval when the API says it is being asked too often', () => {
        expect(nextBackoff(BASE_INTERVAL_MS, rateLimited(null))).toBe(BASE_INTERVAL_MS * 2);
        expect(nextBackoff(BASE_INTERVAL_MS * 2, rateLimited(null))).toBe(BASE_INTERVAL_MS * 4);
    });

    it('waits as long as the API asked when it said how long', () => {
        expect(nextBackoff(BASE_INTERVAL_MS, rateLimited(90))).toBe(90_000);
    });

    it('never backs off past the ceiling', () => {
        expect(nextBackoff(MAX_INTERVAL_MS, rateLimited(null))).toBe(MAX_INTERVAL_MS);
        expect(nextBackoff(BASE_INTERVAL_MS, rateLimited(10_000))).toBe(MAX_INTERVAL_MS);
    });

    it('leaves the interval alone for a dropped connection', () => {
        const offline = new ApiError({
            kind: 'network',
            status: null,
            message: 'offline',
            fields: {},
            retryAfter: null,
            body: null,
        });

        // Backing off here would leave a Player stale long after the wifi came
        // back, which is the opposite of what this screen is for.
        expect(nextBackoff(60_000, offline)).toBe(BASE_INTERVAL_MS);
    });

    it('returns to the base interval once a poll succeeds', () => {
        expect(nextBackoff(MAX_INTERVAL_MS, null)).toBe(BASE_INTERVAL_MS);
    });
});

describe('reading the pulse', () => {
    it('treats the first reading as no change at all', () => {
        // Everything is new on the first poll, and calling that a change would
        // refetch every expensive resource the screen has just loaded.
        expect(movedStamps(null, pulse())).toEqual([]);
    });

    it('notices nothing when nothing moved', () => {
        expect(movedStamps(pulse(), pulse())).toEqual([]);
    });

    it('notices a round being published', () => {
        const before = pulse({ current_round: null, rounds: null });

        expect(movedStamps(before, pulse())).toContain('rounds');
    });

    it('notices the current round changing even if the stamp somehow did not', () => {
        const before = pulse({ current_round: { id: 3, number: 1 } });

        expect(movedStamps(before, pulse())).toEqual(['rounds']);
    });

    it('notices a result landing without claiming the rounds moved', () => {
        const after = pulse({ standings: '2026-09-12T15:00:00Z' });

        expect(movedStamps(pulse(), after)).toEqual(['standings']);
    });

    it('notices a poll opening', () => {
        const after = pulse({ polls: '2026-09-12T16:00:00Z' });

        expect(movedStamps(pulse(), after)).toEqual(['polls']);
    });

    it('reports every stamp that moved at once', () => {
        const after = pulse({
            current_round: { id: 5, number: 3 },
            rounds: '2026-09-12T17:00:00Z',
            standings: '2026-09-12T17:00:01Z',
            polls: '2026-09-12T17:00:02Z',
        });

        expect(movedStamps(pulse(), after)).toEqual(['rounds', 'standings', 'polls']);
    });
});
