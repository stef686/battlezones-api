import type { OrganisedEvent } from '@/api/events';

export type WhenTabId = 'happening-now' | 'upcoming' | 'past';

export interface WhenTab {
    id: WhenTabId;
    name: string;
    events: OrganisedEvent[];
}

/**
 * The Organiser's events split by when they are, in the order the tabs sit.
 *
 * Grouped by the Event's dates on the viewer's clock, never by its status:
 * nothing moves an Event to Active or Completed by itself, so a status would
 * leave last month's Published Event in "Upcoming" for ever. A Draft or a
 * Cancelled Event sits wherever its dates put it, and keeps its badge.
 *
 * Under way from the instant it starts until the instant it ends. An Event
 * with no start yet is upcoming; one that has started with no end is still
 * on. Within a tab, what needs the Organiser soonest comes first: the Event
 * ending soonest, the one starting soonest, the one that ended last. `now` is
 * the caller's so a test can hold the clock still.
 */
export function whenTabs(events: OrganisedEvent[], now: Date = new Date()): WhenTab[] {
    const at = now.getTime();
    const happening: OrganisedEvent[] = [];
    const upcoming: OrganisedEvent[] = [];
    const past: OrganisedEvent[] = [];

    for (const event of events) {
        const starts = moment(event.starts_at);
        const ends = moment(event.ends_at);

        if (starts === null || starts > at) {
            upcoming.push(event);
        } else if (ends === null || ends > at) {
            happening.push(event);
        } else {
            past.push(event);
        }
    }

    return [
        { id: 'happening-now', name: 'Happening now', events: happening.sort((a, b) => soonest(a.ends_at, b.ends_at)) },
        { id: 'upcoming', name: 'Upcoming', events: upcoming.sort((a, b) => soonest(a.starts_at, b.starts_at)) },
        // Every past Event has an end: one without is still on.
        { id: 'past', name: 'Past', events: past.sort((a, b) => soonest(b.ends_at, a.ends_at)) },
    ];
}

function moment(iso: string | null): number | null {
    return iso === null ? null : new Date(iso).getTime();
}

/** Earlier first, with a date not yet set after every date that is. */
function soonest(left: string | null, right: string | null): number {
    return (moment(left) ?? Number.POSITIVE_INFINITY) - (moment(right) ?? Number.POSITIVE_INFINITY) || 0;
}
