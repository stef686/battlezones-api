import type { ApiClient } from './client';

export interface Standing {
    id: number;
    position: number;
    attendee: { id: number; name: string };
    scores: { value: number | string; score_type: { slug: string; name: string } }[];
}

export function fetchStandings(client: ApiClient, slug: string): Promise<Standing[]> {
    return client.get<{ data: Standing[] }>(`/api/events/${encodeURIComponent(slug)}/standings`)
        .then((response) => response.data);
}

/** The score a Standing carries under one Score Type, or a dash where it has none. */
export function scoreOf(standing: Standing, slug: string): string {
    const found = standing.scores.find((entry) => entry.score_type.slug === slug);

    return found === undefined ? '—' : String(Number(found.value));
}

/** Position by Attendee id, for screens that show standings beside something else. */
export function positionsByAttendee(standings: Standing[]): Map<number, number> {
    return new Map(standings.map((standing) => [standing.attendee.id, standing.position]));
}
