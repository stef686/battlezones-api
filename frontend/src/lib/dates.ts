/**
 * Dates as a Player reads them on a phone: short, local, and without a year
 * unless the Event actually crosses one.
 */
export function formatDateRange(start: string | null, end: string | null): string {
    if (start === null) {
        return '';
    }

    const from = new Date(start);
    const to = end === null ? null : new Date(end);

    if (Number.isNaN(from.getTime())) {
        return '';
    }

    if (to === null || Number.isNaN(to.getTime()) || sameDay(from, to)) {
        return full(from);
    }

    return `${sameMonth(from, to) ? day(from) : short(from)} – ${full(to)}`;
}

function sameDay(a: Date, b: Date): boolean {
    return a.toDateString() === b.toDateString();
}

function sameMonth(a: Date, b: Date): boolean {
    return a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth();
}

function day(date: Date): string {
    return date.toLocaleDateString(undefined, { day: 'numeric' });
}

function short(date: Date): string {
    return date.toLocaleDateString(undefined, { day: 'numeric', month: 'short' });
}

function full(date: Date): string {
    return date.toLocaleDateString(undefined, { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
}

/**
 * The time as the hall reads it.
 *
 * Schedule times carry the Event's own UTC offset, so the wall clock is taken
 * straight out of the string rather than through a Date: converting it would
 * show a Player abroad the time lunch happens where they are standing, which
 * is never what a schedule means.
 */
export function wallClockTime(iso: string): string {
    return iso.slice(11, 16);
}

/**
 * A schedule day heading, from the plain `YYYY-MM-DD` the API groups by.
 *
 * Parsed field by field rather than through `new Date(...)`, which reads a
 * bare date as UTC midnight and so shows the day before to anyone west of it.
 */
export function formatDay(date: string): string {
    const [year, month, day] = date.split('-').map(Number);

    if (year === undefined || month === undefined || day === undefined) {
        return date;
    }

    return new Date(year, month - 1, day)
        .toLocaleDateString(undefined, { weekday: 'long', day: 'numeric', month: 'long' });
}
