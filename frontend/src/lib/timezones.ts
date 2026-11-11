/**
 * How a timezone reads, and the current name for one a browser reports.
 *
 * The list an Event's zone is picked from comes from the API (`fetchTimezones`),
 * because that is the list it validates against.
 */

/**
 * Old names some browsers still report for their own zone, against the names
 * the IANA database uses now. Browsers take their zone names from CLDR, which
 * kept these for compatibility; a server's PHP may not know the old ones at
 * all. Only the wizard's default passes through here — anything picked comes
 * from the API's own list.
 */
const CURRENT_NAMES: Readonly<Record<string, string>> = {
    'Africa/Asmera': 'Africa/Asmara',
    'America/Buenos_Aires': 'America/Argentina/Buenos_Aires',
    'America/Catamarca': 'America/Argentina/Catamarca',
    'America/Coral_Harbour': 'America/Atikokan',
    'America/Cordoba': 'America/Argentina/Cordoba',
    'America/Godthab': 'America/Nuuk',
    'America/Indianapolis': 'America/Indiana/Indianapolis',
    'America/Jujuy': 'America/Argentina/Jujuy',
    'America/Louisville': 'America/Kentucky/Louisville',
    'America/Mendoza': 'America/Argentina/Mendoza',
    'Asia/Calcutta': 'Asia/Kolkata',
    'Asia/Katmandu': 'Asia/Kathmandu',
    'Asia/Rangoon': 'Asia/Yangon',
    'Asia/Saigon': 'Asia/Ho_Chi_Minh',
    'Atlantic/Faeroe': 'Atlantic/Faroe',
    'Europe/Kiev': 'Europe/Kyiv',
    'Pacific/Enderbury': 'Pacific/Kanton',
    'Pacific/Ponape': 'Pacific/Pohnpei',
    'Pacific/Truk': 'Pacific/Chuuk',
    'Etc/UTC': 'UTC',
    'Etc/GMT': 'UTC',
    GMT: 'UTC',
};

/** The name the IANA database uses now for a zone a browser reported. */
export function currentZoneName(zone: string): string {
    return CURRENT_NAMES[zone] ?? zone;
}

const labels = new Map<string, string>();

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
        // A zone newer than this browser's data: shown by name rather than
        // refusing to draw the field.
        return name;
    }
}
