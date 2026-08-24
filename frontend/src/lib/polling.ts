import type { ApiError } from '@/api/errors';

/** Often enough that a published Round feels immediate on a phone in a hall. */
export const BASE_INTERVAL_MS = 15_000;

/** Far enough apart that a throttled client stops making the throttling worse. */
export const MAX_INTERVAL_MS = 120_000;

/**
 * How long to wait before the next poll, or `false` to stop entirely.
 *
 * Nothing moves at an Event that has not started or has finished, so polling
 * it is pure cost — to the venue's wifi, and to a phone that has to last the
 * day. Hidden tabs stop for the same reason: a Player who has switched to
 * their messages is not reading Standings.
 */
export function pollInterval(options: { inProgress: boolean; visible: boolean; backoffMs: number }): number | false {
    if (!options.inProgress || !options.visible) {
        return false;
    }

    return Math.max(BASE_INTERVAL_MS, options.backoffMs);
}

/**
 * The next interval after a failure, in milliseconds.
 *
 * Being rate limited is the API saying "you are asking too often", so asking
 * again at the same rate is the one response guaranteed to be wrong. The
 * server's own `Retry-After` wins where it gave one; otherwise the interval
 * doubles, up to a ceiling.
 *
 * Anything else — a dropped connection, a 500 — leaves the interval alone.
 * Backing off there would leave a Player stale long after the wifi came back.
 */
export function nextBackoff(current: number, error: ApiError | null): number {
    if (error === null || error.kind !== 'rate_limited') {
        return BASE_INTERVAL_MS;
    }

    const doubled = Math.min(Math.max(current, BASE_INTERVAL_MS) * 2, MAX_INTERVAL_MS);
    const asked = error.retryAfter === null ? 0 : error.retryAfter * 1000;

    return Math.min(Math.max(doubled, asked), MAX_INTERVAL_MS);
}
