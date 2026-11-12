import { describe, expect, it } from 'vitest';

import type { OrganisedEvent } from '@/api/events';
import { whenTabs } from '@/lib/organised-events';

const NOW = new Date('2026-09-12T12:00:00Z');

function event(id: number, starts_at: string, ends_at: string): OrganisedEvent {
    return { id, name: `Event ${id}`, slug: `event-${id}`, starts_at, ends_at } as OrganisedEvent;
}

function idsIn(events: OrganisedEvent[]) {
    return Object.fromEntries(whenTabs(events, NOW).map((tab) => [tab.id, tab.events.map(({ id }) => id)]));
}

describe('grouping an organiser\'s events by when they are', () => {
    it('offers the tabs in order: happening now, upcoming, past', () => {
        expect(whenTabs([], NOW).map(({ id, name }) => [id, name])).toEqual([
            ['happening-now', 'Happening now'],
            ['upcoming', 'Upcoming'],
            ['past', 'Past'],
        ]);
    });

    it('puts each event in exactly one tab by its dates, the boundaries included', () => {
        expect(idsIn([
            event(1, '2026-09-12T12:00:00Z', '2026-09-12T18:00:00Z'), // starts this instant: under way
            event(2, '2026-09-12T12:00:01Z', '2026-09-12T18:00:00Z'), // a second away: not yet
            event(3, '2026-09-12T09:00:00Z', '2026-09-12T12:00:00Z'), // ends this instant: over
            event(4, '2026-09-12T09:00:00Z', '2026-09-12T12:00:01Z'), // a second left: still on
        ])).toEqual({ 'happening-now': [4, 1], upcoming: [2], past: [3] });
    });

    it('orders happening now by soonest ending, upcoming by soonest start, past by most recent', () => {
        expect(idsIn([
            event(1, '2026-09-11T09:00:00Z', '2026-09-13T18:00:00Z'),
            event(2, '2026-09-12T09:00:00Z', '2026-09-12T18:00:00Z'),
            event(3, '2026-12-01T09:00:00Z', '2026-12-01T18:00:00Z'),
            event(4, '2026-10-01T09:00:00Z', '2026-10-01T18:00:00Z'),
            event(5, '2026-01-01T09:00:00Z', '2026-01-01T18:00:00Z'),
            event(6, '2026-08-01T09:00:00Z', '2026-08-01T18:00:00Z'),
        ])).toEqual({ 'happening-now': [2, 1], upcoming: [4, 3], past: [6, 5] });
    });
});
