/**
 * The Event nav's sections and the look of its chips, shared by the nav itself
 * and by the wizard's header preview, so the preview cannot drift from the
 * strip it stands in for.
 */

export interface EventNavSection {
    name: string;
    label: string;
}

/** The five sections every Event offers, Home first. */
export const EVENT_SECTIONS: readonly EventNavSection[] = [
    { name: 'event', label: 'Home' },
    { name: 'rounds', label: 'Rounds' },
    { name: 'standings', label: 'Standings' },
    { name: 'attendees', label: 'Attendees' },
    { name: 'schedule', label: 'Schedule' },
];

/**
 * A chip's classes, lit or not. The lit one is underlined in the Event
 * Colour's primary rather than filled: a tab strip reads as where you are,
 * where a pill reads as a button.
 */
export function eventNavChipClass(lit: boolean): string[] {
    return [
        'block border-b-2 px-2 py-3 text-sm font-medium whitespace-nowrap focus:outline-hidden',
        lit
            ? 'border-primary text-navbar-nav-foreground'
            : 'border-transparent text-muted-foreground-1 hover:text-navbar-nav-foreground focus:text-navbar-nav-foreground',
    ];
}
