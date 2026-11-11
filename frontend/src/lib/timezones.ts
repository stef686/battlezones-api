/**
 * The timezones an Event can be run in, and how each one reads.
 *
 * The list is the browser's, so it stays as current as the device's own
 * zone data. The API accepts every name in it, the old backward-compatible
 * ones included.
 */

let zones: string[] | null = null;
const labels = new Map<string, string>();

/** Every IANA zone this browser knows, in its own order. */
export function timezones(): string[] {
    zones ??= Intl.supportedValuesOf('timeZone');

    return zones;
}

/**
 * A zone as a person reads it: the underscores of the identifier turned to
 * spaces, and its offset from UTC, so "America/New_York" reads as
 * "America/New York (GMT-5)".
 *
 * Worked out once a page load: there are some four hundred zones, a search
 * relabels every one of them per keystroke, and an offset that moves at a
 * clock change mid-session is not worth a formatter per zone per key.
 */
export function timezoneLabel(zone: string): string {
    let label = labels.get(zone);

    if (label === undefined) {
        label = labelOf(zone);
        labels.set(zone, label);
    }

    return label;
}

function labelOf(zone: string): string {
    const name = zone.replaceAll('_', ' ');

    try {
        const offset = new Intl.DateTimeFormat('en-GB', { timeZone: zone, timeZoneName: 'shortOffset' })
            .formatToParts(new Date())
            .find((part) => part.type === 'timeZoneName')?.value;

        return offset === undefined ? name : `${name} (${offset})`;
    } catch {
        // A zone this browser does not know, held from elsewhere: shown by
        // name rather than refusing to draw the field.
        return name;
    }
}
