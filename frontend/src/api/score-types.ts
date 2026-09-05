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
