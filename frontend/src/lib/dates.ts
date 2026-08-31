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
 * A schedule day, short enough to be a tab: "26th Wed".
 *
 * From the plain `YYYY-MM-DD` the API groups by, parsed field by field rather
 * than through `new Date(...)`, which reads a bare date as UTC midnight and so
 * names the day before to anyone west of it.
 *
 * The date leads because a Player checking a schedule knows which day of the
 * Event they are standing in, not which weekday it happens to be, and two tabs
 * reading "Sat" and "Sun" are two tabs that look alike at a glance.
 */
export function shortDay(date: string): string {
    const [year, month, day] = date.split('-').map(Number);

    if (year === undefined || month === undefined || day === undefined) {
        return date;
    }

    const weekday = new Date(year, month - 1, day).toLocaleDateString(undefined, { weekday: 'short' });

    return `${day}${ordinal(day)} ${weekday}`;
}

/** English ordinal suffix: 1st, 2nd, 3rd, 4th, and the 11th–13th exceptions. */
function ordinal(day: number): string {
    if (day >= 11 && day <= 13) {
        return 'th';
    }

    return { 1: 'st', 2: 'nd', 3: 'rd' }[day % 10] ?? 'th';
}
