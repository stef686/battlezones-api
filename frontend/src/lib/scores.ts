/**
 * A score written the way somebody says it out loud.
 *
 * Scores arrive as decimal strings — the column holds two places so half
 * points survive — and "3.00" is noise on a card a Player reads at a glance.
 * A whole number loses its decimals; anything else keeps only the places it
 * actually uses, so 3.5 stays 3.5.
 *
 * A value that is not a number is handed back untouched rather than rendered
 * as NaN: a Score Type could one day hold something that is not counted.
 */
export function formatScore(value: number | string): string {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? String(parsed) : String(value);
}
