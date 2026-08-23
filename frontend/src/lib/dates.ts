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
