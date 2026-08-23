import type { ApiClient } from './client';

export interface EventViewer {
    is_organiser: boolean;
    is_lead_organiser: boolean;
    is_attendee: boolean;
    attendee_id: number | null;
    permissions: {
        organise: boolean;
        register: boolean;
        manage_organisers: boolean;
    };
}

export interface EventSummary {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    status: string;
    starts_at: string | null;
    ends_at: string | null;
    max_attendees: number | null;
    /** How many Players make up one party. Two for a doubles Event. */
    attendee_size: number;
    requires_allegiance: boolean;
    registration_closes_at: string | null;
    attendees_count?: number;
    is_full: boolean;
    game_system: { id: number; name: string; slug: string } | null;
    viewer: EventViewer | null;
}

export interface Faction {
    id: number;
    name: string;
    slug: string;
}

export interface AttendeeMember {
    id: number;
    name: string;
    faction: { id: number; name: string } | null;
}

export interface Attendee {
    id: number;
    name: string | null;
    allegiance: string | null;
    members: AttendeeMember[];
    checked_in_at: string | null;
}

export type Allegiance = 'loyalist' | 'traitor';

export interface PlayerEntry {
    name?: string | null;
    email: string;
    faction_id?: number | null;
}

export interface RegistrationDetails {
    name?: string | null;
    allegiance?: Allegiance | null;
    players: PlayerEntry[];
}

export function fetchEvent(client: ApiClient, slug: string): Promise<EventSummary> {
    return client.get<{ data: EventSummary }>(eventPath(slug)).then((response) => response.data);
}

export function fetchFactions(client: ApiClient, slug: string): Promise<Faction[]> {
    return client.get<{ data: Faction[] }>(`${eventPath(slug)}/factions`).then((response) => response.data);
}

export function fetchAttendee(client: ApiClient, slug: string, attendeeId: number): Promise<Attendee> {
    return client.get<{ data: Attendee }>(`${eventPath(slug)}/attendees/${attendeeId}`).then((response) => response.data);
}

/**
 * Enter a party for the Event.
 *
 * Every Player is named at once, including the ones with no account: the API
 * invites those, which is what makes the team complete and pairable the day it
 * registers rather than the day its last member reads their email.
 */
export function registerAttendee(client: ApiClient, slug: string, details: RegistrationDetails): Promise<Attendee> {
    return client.post<{ data: Attendee }>(`${eventPath(slug)}/attendees`, {
        ...(details.name === undefined || details.name === null || details.name === '' ? {} : { name: details.name }),
        ...(details.allegiance === undefined || details.allegiance === null ? {} : { allegiance: details.allegiance }),
        players: details.players.map((player) => ({
            ...(player.name === undefined || player.name === null || player.name === '' ? {} : { name: player.name }),
            email: player.email,
            ...(player.faction_id === undefined || player.faction_id === null ? {} : { faction_id: player.faction_id }),
        })),
    }).then((response) => response.data);
}

export function amendAttendee(
    client: ApiClient,
    slug: string,
    attendeeId: number,
    changes: { name?: string | null; allegiance?: Allegiance | null },
): Promise<Attendee> {
    return client.patch<{ data: Attendee }>(`${eventPath(slug)}/attendees/${attendeeId}`, changes)
        .then((response) => response.data);
}

/**
 * The Faction this Player is bringing — theirs, not the party's, so a doubles
 * team fields two under one Allegiance. Addressed as "mine" rather than by
 * Player id because a Player who has not claimed their account cannot be named
 * in a URL at all.
 */
export function recordMyFaction(client: ApiClient, slug: string, factionId: number | null): Promise<Attendee> {
    return client.patch<{ data: Attendee }>(`${eventPath(slug)}/my-faction`, { faction_id: factionId })
        .then((response) => response.data);
}

function eventPath(slug: string): string {
    return `/api/events/${encodeURIComponent(slug)}`;
}
