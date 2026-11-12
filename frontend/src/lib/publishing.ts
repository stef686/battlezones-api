import type { RouteLocationRaw } from 'vue-router';

import type { EventSummary } from '@/api/events';

/** One thing publishing needs, keyed on the field the API refuses it under. */
export interface PublishCheck {
    field: 'venue_name' | 'venue_city' | 'ends_at';
    label: string;
    met: boolean;
    /** Where an Organiser goes to put it right. */
    fix: RouteLocationRaw;
}

/**
 * What a Draft needs before it can be published, as the organise hub lists it.
 *
 * Mirrors `App\Actions\Events\PublishEvent`, which is the rule: this exists so
 * the button can say what is missing before it is pressed. Change both
 * together. An Event always has an end, so one without is treated as unmet
 * rather than as never ending. `now` is the caller's so a test can hold the clock still.
 */
export function publishChecklist(event: EventSummary, now: Date = new Date()): PublishCheck[] {
    const settings: RouteLocationRaw = { name: 'event-settings', params: { eventSlug: event.slug } };

    return [
        { field: 'venue_name', label: 'Venue name', met: filled(event.venue.name), fix: settings },
        { field: 'venue_city', label: 'Venue town or city', met: filled(event.venue.city), fix: settings },
        { field: 'ends_at', label: 'Not over yet', met: event.ends_at !== null && new Date(event.ends_at) > now, fix: settings },
    ];
}

function filled(value: string | null): boolean {
    return value !== null && value.trim() !== '';
}
