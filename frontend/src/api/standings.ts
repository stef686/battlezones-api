import { formatScore } from '@/lib/scores';

import type { ApiClient } from './client';
import type { AttendeeMember } from './events';

export interface Standing {
    id: number;
    position: number;
    /**
     * Places gained since the Round before the one being played: positive for
     * a climb, negative for a drop, zero for holding. Null until two Rounds
     * have been scored, which is a table with no arrows rather than a table
     * full of dashes.
     *
     * Worked out by the API, which is what knows that a smaller position is a
     * better one.
     */
    movement: number | null;
    attendee: { id: number; name: string; members: AttendeeMember[] };
    scores: { value: number | string; score_type: { slug: string; name: string } }[];
}

export function fetchStandings(client: ApiClient, slug: string): Promise<Standing[]> {
    return client.get<{ data: Standing[] }>(`/api/events/${encodeURIComponent(slug)}/standings`)
        .then((response) => response.data);
}

/** The score a Standing carries under one Score Type, or a dash where it has none. */
export function scoreOf(standing: Standing, slug: string): string {
    const found = standing.scores.find((entry) => entry.score_type.slug === slug);

    return found === undefined ? '—' : formatScore(found.value);
}

/**
 * The Factions a Standing is fielding, named in the order its Players are.
 *
 * A doubles team brings two, and the pair is what tells one team from another
 * at a glance — a name says who, a Faction says what turned up. Players who
 * have not chosen one are left out rather than named as blanks, so a team
 * halfway through registering reads as one Faction and not as a gap.
 */
export function factionsOf(standing: Standing): string {
    return (standing.attendee.members ?? [])
        .map((member) => member.faction?.name)
        .filter((name): name is string => name !== undefined && name !== null)
        .join(' & ');
}

/** Position by Attendee id, for screens that show standings beside something else. */
export function positionsByAttendee(standings: Standing[]): Map<number, number> {
    return new Map(standings.map((standing) => [standing.attendee.id, standing.position]));
}
