import type { ApiClient } from './client';

/**
 * A column an Event is scored on.
 *
 * `is_scored` says whether any Game has been scored under it — the flag the
 * format screen locks a row on, because removing a scored Score Type would
 * take its Game scores with it.
 */
export interface ScoreType {
    id: number;
    name: string;
    slug: string;
    sort_direction: 'asc' | 'desc';
    is_derived: boolean;
    is_primary: boolean;
    counts_for_ranking: boolean;
    ranking_order: number | null;
    win_points: string | null;
    draw_points: string | null;
    loss_points: string | null;
    display_order: number;
    is_scored: boolean;
}

/** The columns this Event is scored on, in the order they are shown. Organisers only. */
export function fetchScoreTypes(client: ApiClient, slug: string): Promise<ScoreType[]> {
    return client.get<{ data: ScoreType[] }>(`/api/events/${slug}/score-types`)
        .then((response) => response.data);
}

/** One Score Type as an Organiser sends it back. Slugs are the server's alone. */
export interface ScoreTypeChange {
    id: number;
    name: string;
    sort_direction: 'asc' | 'desc';
    is_derived: boolean;
    is_primary: boolean;
    counts_for_ranking: boolean;
    win_points: number | null;
    draw_points: number | null;
    loss_points: number | null;
}

/**
 * Replace the whole ordered set in one request.
 *
 * Position sets the display order, and position among the rows counting for
 * ranking sets the ranking order, so the columns and the Standings can never
 * half-land against each other.
 */
export function replaceScoreTypes(client: ApiClient, slug: string, scoreTypes: ScoreTypeChange[]): Promise<ScoreType[]> {
    return client.put<{ data: ScoreType[] }>(`/api/events/${slug}/score-types`, { score_types: scoreTypes })
        .then((response) => response.data);
}
