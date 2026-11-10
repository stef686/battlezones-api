/**
 * The shapes the Event field components edit.
 *
 * Kept out of the components so a screen can build, compare and send these
 * without rendering anything: the settings and format screens read them from
 * the Event, and the create-event wizard starts them blank.
 *
 * Every value is the string its input holds. Turning `''` into null, a
 * datetime-local into an ISO moment and a points field into a number is the
 * sending screen's business, because only it knows the endpoint.
 */
import type { ScoreType, ScoreTypeChange } from '@/api/score-types';

/** Edited by `EventDetailsFields`. Moments are `YYYY-MM-DDTHH:mm`, as a datetime-local input holds them. */
export interface EventDetails {
    name: string;
    starts_at: string;
    ends_at: string;
}

/** Edited by `EventVenueFields`. `venue_country` is an ISO code, blank for none. */
export interface EventVenue {
    venue_name: string;
    venue_address: string;
    venue_city: string;
    venue_country: string;
}

/** Edited by `EventShapeFields`. Numbers are text while being typed; blank places means no limit. */
export interface EventShape {
    game_system_id: string;
    attendee_size: string;
    max_attendees: string;
}

/** The messages a component shows, keyed by the field names of the shape it edits. */
export type FieldErrors = Record<string, string[]>;

/** A Score Type as `ScoreTypeListEditor` edits it: points are text while being typed. */
export interface ScoreTypeRow {
    /** Null on a column being added, which the API creates. */
    id: number | null;
    /** Stable for the life of the row, so a reorder does not remount its inputs. */
    key: string;
    name: string;
    /** Blank means the API works one out from the name. */
    abbreviation: string;
    sort_direction: 'asc' | 'desc';
    is_derived: boolean;
    is_primary: boolean;
    counts_for_ranking: boolean;
    win_points: string;
    draw_points: string;
    loss_points: string;
    is_scored: boolean;
}

export function scoreTypeRowOf(scoreType: ScoreType): ScoreTypeRow {
    return {
        id: scoreType.id,
        key: `saved-${scoreType.id}`,
        name: scoreType.name,
        abbreviation: scoreType.abbreviation,
        sort_direction: scoreType.sort_direction,
        is_derived: scoreType.is_derived,
        is_primary: scoreType.is_primary,
        counts_for_ranking: scoreType.counts_for_ranking,
        win_points: scoreType.win_points ?? '',
        draw_points: scoreType.draw_points ?? '',
        loss_points: scoreType.loss_points ?? '',
        is_scored: scoreType.is_scored,
    };
}

let added = 0;

/** A blank column, ready to be named. */
export function blankScoreTypeRow(): ScoreTypeRow {
    return {
        id: null,
        key: `new-${++added}`,
        name: '',
        abbreviation: '',
        sort_direction: 'desc',
        is_derived: false,
        is_primary: false,
        counts_for_ranking: true,
        win_points: '',
        draw_points: '',
        loss_points: '',
        is_scored: false,
    };
}

function pointsOf(value: string): number | null {
    return value === '' ? null : Number(value);
}

/** A row as the API takes it back. Points only travel on a calculated column. */
export function scoreTypeChangeOf(row: ScoreTypeRow): ScoreTypeChange {
    return {
        id: row.id,
        name: row.name,
        abbreviation: row.abbreviation,
        sort_direction: row.sort_direction,
        is_derived: row.is_derived,
        is_primary: row.is_primary,
        counts_for_ranking: row.counts_for_ranking,
        win_points: row.is_derived ? pointsOf(row.win_points) : null,
        draw_points: row.is_derived ? pointsOf(row.draw_points) : null,
        loss_points: row.is_derived ? pointsOf(row.loss_points) : null,
    };
}

/**
 * Narrow a 422's messages to the ones under `prefix.`, keyed without it —
 * `score_types.0.name` becomes `0.name` — so an editor never learns which
 * endpoint's body it sits in.
 */
export function errorsUnder(errors: FieldErrors, prefix: string): FieldErrors {
    const lead = `${prefix}.`;

    return Object.fromEntries(Object.entries(errors)
        .filter(([field]) => field.startsWith(lead))
        .map(([field, messages]) => [field.slice(lead.length), messages]));
}
