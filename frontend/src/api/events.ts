import type { ApiClient } from './client';
import type { ScoreTypeChange } from './score-types';

export interface EventViewer {
    is_organiser: boolean;
    is_lead_organiser: boolean;
    is_attendee: boolean;
    attendee_id: number | null;
    permissions: {
        organise: boolean;
        register: boolean;
        manage_organisers: boolean;
        /** Take a Draft to Published: the Lead Organiser's alone. Sent on a single Event read only. */
        publish?: boolean;
        /**
         * Whether the Event can go back to Draft right now: the Lead's call,
         * and only while nobody has entered or been invited. Sent on a single
         * Event read only.
         */
        unpublish?: boolean;
    };
}

/** Where an Event is in its life. Draft and Cancelled are seen only by its Organisers. */
export type EventStatus = 'draft' | 'published' | 'active' | 'completed' | 'cancelled';

export interface EventSummary {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    status: EventStatus;
    starts_at: string | null;
    ends_at: string | null;
    max_attendees: number | null;
    /** How many Players make up one party. Two for a doubles Event. */
    attendee_size: number;
    requires_allegiance: boolean;
    /** The wall clock the Event is run on, which every schedule time is in. */
    timezone: string;
    /**
     * Whether a Schedule block now holds the timezone in place. Sent with a
     * single Event read for its viewer, never on a listing.
     */
    timezone_is_fixed?: boolean;
    registration_closes_at: string | null;
    attendees_count?: number;
    is_full: boolean;
    game_system: { id: number; name: string; slug: string } | null;
    /** Both normalised variants, or null while the header is flat. */
    banner: { large: string; small: string } | null;
    /** The Event Colour as a lowercase `#rrggbb`, or null for the platform colour. */
    colour: string | null;
    venue: {
        name: string | null;
        address: string | null;
        city: string | null;
        country: string | null;
    };
    documents: EventDocument[];
    viewer: EventViewer | null;
}

/**
 * A row of the Events the viewer organises. The listing carries no viewer
 * block and no documents: every row is the viewer's to run, and the row
 * leads to the hub, which reads the Event in full.
 */
export type OrganisedEvent = Omit<EventSummary, 'viewer' | 'documents' | 'timezone_is_fixed'>;

export interface EventDocument {
    id: number;
    name: string;
    url: string;
    created_at: string | null;
}

export interface ScheduleBlock {
    id: number;
    label: string;
    type: string;
    /** Carries the Event's own offset: read the wall clock, never convert it. */
    starts_at: string;
    ends_at: string;
    display_order: number;
    target_id: number | null;
    /**
     * Where the thing this block describes has got to: the Round being played
     * or the open Poll is `live`, one the Event has moved past is `finished`,
     * and anything not yet under way is null.
     */
    target_state: 'live' | 'finished' | null;
    round: { id: number; number: number; name: string | null; status: string } | null;
}

export interface ScheduleDay {
    date: string;
    blocks: ScheduleBlock[];
}

export interface AttendeeSummary {
    id: number;
    name: string;
    allegiance: string | null;
    /** The team's badge, signed and expiring. Null where they have not uploaded one. */
    avatar?: string | null;
    /** When an Organiser marked the team as having left; null while it is in. */
    dropped_at?: string | null;
    members: AttendeeMember[];
}

export interface Page<T> {
    data: T[];
    meta: { current_page: number; last_page: number; total: number };
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
    /**
     * The seat rather than the Player, sent only to the team and its
     * Organisers. It addresses a Player who has not claimed their account,
     * which no route can name.
     */
    membership_id?: number;
    /** Whether this Player has yet to answer their invitation. */
    invite_outstanding?: boolean;
    /**
     * Where that invitation was sent. Present only while it is outstanding —
     * a claimed account's address belongs to its owner, not to their team.
     */
    email?: string;
    /** Whether this Player's list is in. Says nothing about what it holds. */
    army_list_locked?: boolean;
    /**
     * Absent where the reader may not see it — an unrevealed team's lists are
     * not theirs — and null where the Player has not written one.
     */
    army_list?: string | null;
}

export interface Attendee {
    id: number;
    name: string | null;
    allegiance: string | null;
    /** The team's badge, signed and expiring. Null where they have not uploaded one. */
    avatar?: string | null;
    /** Whether the Event has begun, which is what freezes the side it fights for. */
    allegiance_locked?: boolean;
    /**
     * Whether the team is in any Game, Draft Rounds included: until then an
     * Organiser deletes it, afterwards they drop it. Sent on the detail only.
     */
    has_been_paired?: boolean;
    /** When an Organiser marked the team as having left; null while it is in. */
    dropped_at?: string | null;
    /**
     * Whether a Draft Round already pairs the team. A Drop leaves that
     * pairing as it is, so the Organiser is told before dropping.
     */
    paired_in_draft_round?: boolean;
    members: AttendeeMember[];
    checked_in_at: string | null;
    /** Whether this army is on the display table for the painting vote. */
    painting_entered?: boolean;
    /** The number it sits under there, assigned by whoever laid the table out. */
    display_number?: number | null;
}

export type Allegiance = 'loyalist' | 'traitor';

export interface PlayerEntry {
    name?: string | null;
    email: string;
    faction_id?: number | null;
    /** Taken with the entry by an Organiser; a Player submits theirs later. */
    army_list?: string | null;
}

export interface RegistrationDetails {
    name?: string | null;
    allegiance?: Allegiance | null;
    players: PlayerEntry[];
}

/**
 * The Event fields an Organiser may change.
 *
 * Deliberately narrower than the Event itself: the slug is in Invite emails
 * already sent, the party size is the shape every registration was built at,
 * and status and pairing format drive Round generation rather than
 * presentation. See docs/adr/0002-general-event-patch-endpoint.md.
 */
export interface EventChanges {
    name?: string;
    description?: string | null;
    venue_name?: string | null;
    venue_address?: string | null;
    venue_city?: string | null;
    venue_country?: string | null;
    starts_at?: string | null;
    ends_at?: string | null;
    registration_closes_at?: string | null;
    max_attendees?: number | null;
    /** The Event Colour, editable whatever the Event's state. Null for the platform colour. */
    colour?: string | null;
    /** An IANA zone. Refused by the API once the Event has a Schedule block. */
    timezone?: string;
    /** Refused by the API once anybody has entered. */
    game_system_id?: number;
    /** How many Players make an Attendee. Refused once anybody has entered. */
    attendee_size?: number;
}

/** How Rounds are paired. Chosen when an Event is created, and never again (ADR 0006). */
export type PairingFormat = 'swiss' | 'round_robin' | 'knockout' | 'random';

/** An Event as the create wizard sends it, whole. Status is never sent: every Event starts as a Draft. */
export interface NewEvent {
    name: string;
    /** Permanent from the moment it exists. */
    slug: string;
    starts_at: string;
    ends_at: string;
    /** The IANA zone the venue keeps. */
    timezone: string;
    venue_name: string | null;
    venue_address: string | null;
    venue_city: string | null;
    venue_country: string | null;
    game_system_id: number;
    attendee_size: number;
    max_attendees: number | null;
    /** The Event Colour, lowercase `#rrggbb`. Null for the platform colour. */
    colour: string | null;
    /** Only a format the API has built. Permanent from the moment it exists. */
    pairing_format: PairingFormat;
    settings: {
        /** Null for open-ended. */
        round_count: number | null;
        requires_opposed_allegiance: boolean;
    };
    score_types: ScoreTypeChange[];
}

/**
 * Create an Event, with the caller as its Lead Organiser and the Score Types
 * it is ranked on, in one request. A Banner goes up afterwards on its own
 * route, because a JSON body carries no file.
 */
export function createEvent(client: ApiClient, event: NewEvent): Promise<EventSummary> {
    return client.post<{ data: EventSummary }>('/api/events', event).then((response) => response.data);
}

/**
 * Whether a new Event could be created at a slug. Drafts hold slugs too, which
 * is why this is asked here rather than of the Event route, which answers 404
 * for a Draft whether or not it exists.
 */
export function checkEventSlug(client: ApiClient, slug: string): Promise<{ slug: string; available: boolean }> {
    const query = new URLSearchParams({ slug });

    return client.get<{ data: { slug: string; available: boolean } }>(`/api/event-slug-availability?${query.toString()}`)
        .then((response) => response.data);
}

export function updateEvent(client: ApiClient, slug: string, changes: EventChanges): Promise<EventSummary> {
    return client.patch<{ data: EventSummary }>(eventPath(slug), changes).then((response) => response.data);
}

/**
 * Take a Draft to Published, so Players can find and enter it.
 *
 * Its own transition rather than a field on the Event PATCH (ADR 0002). The
 * API refuses each unmet precondition under the field that fixes it, which
 * `publishChecklist` in `lib/publishing.ts` mirrors.
 */
export function publishEvent(client: ApiClient, slug: string): Promise<EventSummary> {
    return client.post<{ data: EventSummary }>(`${eventPath(slug)}/publish`).then((response) => response.data);
}

/**
 * Take a Published Event back to Draft, undoing a publish made too early.
 *
 * The reverse of `publishEvent`, on the same path. The API refuses once
 * anybody has entered or an Invite is outstanding, when cancelling is the
 * way out instead.
 */
export function unpublishEvent(client: ApiClient, slug: string): Promise<EventSummary> {
    return client.delete<{ data: EventSummary }>(`${eventPath(slug)}/publish`).then((response) => response.data);
}

/**
 * Upload a Banner.
 *
 * Multipart and a route of its own rather than a field on the Event PATCH,
 * because PHP does not populate uploaded files for a PATCH body. The image is
 * normalised and the original discarded, so re-framing means uploading again.
 */
export function uploadBanner(client: ApiClient, slug: string, file: File): Promise<EventSummary> {
    const form = new FormData();
    form.set('banner', file);

    return client.post<{ data: EventSummary }>(`${eventPath(slug)}/banner`, form).then((response) => response.data);
}

/** Take the Banner off, returning the header to its flat surface. */
export function removeBanner(client: ApiClient, slug: string): Promise<EventSummary> {
    return client.delete<{ data: EventSummary }>(`${eventPath(slug)}/banner`).then((response) => response.data);
}

export function fetchEvent(client: ApiClient, slug: string): Promise<EventSummary> {
    return client.get<{ data: EventSummary }>(eventPath(slug)).then((response) => response.data);
}

/** Every Event the viewer runs, in every status, ordered by start. */
export function fetchOrganisedEvents(client: ApiClient): Promise<OrganisedEvent[]> {
    return client.get<{ data: OrganisedEvent[] }>('/api/organised-events').then((response) => response.data);
}

export function fetchSchedule(client: ApiClient, slug: string): Promise<ScheduleDay[]> {
    return client.get<{ data: ScheduleDay[] }>(`${eventPath(slug)}/schedule`).then((response) => response.data);
}

export function fetchAttendees(client: ApiClient, slug: string, options: { search?: string; page?: number } = {}): Promise<Page<AttendeeSummary>> {
    const query = new URLSearchParams();

    if (options.search !== undefined && options.search !== '') {
        query.set('search', options.search);
    }

    if (options.page !== undefined && options.page > 1) {
        query.set('page', String(options.page));
    }

    const suffix = query.toString() === '' ? '' : `?${query.toString()}`;

    return client.get<Page<AttendeeSummary>>(`${eventPath(slug)}/attendees${suffix}`);
}

export interface NewScheduleBlock {
    label: string;
    type: string;
    starts_at: string;
    ends_at: string;
    round_id?: number | null;
}

/** Put one more block on the schedule. Organisers only, as the API enforces. */
export function addScheduleBlock(client: ApiClient, slug: string, block: NewScheduleBlock): Promise<ScheduleBlock> {
    return client.post<{ data: ScheduleBlock }>(`${eventPath(slug)}/schedule`, {
        label: block.label,
        type: block.type,
        starts_at: block.starts_at,
        ends_at: block.ends_at,
        ...(block.round_id === undefined || block.round_id === null ? {} : { round_id: block.round_id }),
    }).then((response) => response.data);
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
            ...(player.army_list === undefined || player.army_list === null || player.army_list === '' ? {} : { army_list: player.army_list }),
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

/**
 * Upload a team's Avatar.
 *
 * Multipart and a route of its own for the same reason the Banner is: PHP does
 * not populate uploaded files for a PATCH body. The square is cut on the way
 * in and the original discarded.
 */
export function uploadTeamAvatar(client: ApiClient, slug: string, attendeeId: number, file: File): Promise<Attendee> {
    const form = new FormData();
    form.set('avatar', file);

    return client.post<{ data: Attendee }>(`${eventPath(slug)}/attendees/${attendeeId}/avatar`, form)
        .then((response) => response.data);
}

/**
 * Remove a never-paired Attendee from the Event outright, with its Invites
 * and ballots. Refused with 422 once it has been paired.
 */
export function deleteAttendee(client: ApiClient, slug: string, attendeeId: number): Promise<void> {
    return client.delete<null>(`${eventPath(slug)}/attendees/${attendeeId}`).then(() => undefined);
}

/**
 * Mark a paired Attendee as having left: not paired from the next Round
 * generated, with everything already played left standing.
 */
export function dropAttendee(client: ApiClient, slug: string, attendeeId: number): Promise<Attendee> {
    return client.post<{ data: Attendee }>(`${eventPath(slug)}/attendees/${attendeeId}/drop`)
        .then((response) => response.data);
}

/** Take a Drop back: the Attendee is paired again from the next Round generated. */
export function undropAttendee(client: ApiClient, slug: string, attendeeId: number): Promise<Attendee> {
    return client.delete<{ data: Attendee }>(`${eventPath(slug)}/attendees/${attendeeId}/drop`)
        .then((response) => response.data);
}

/** Take the Avatar off, returning the team to its placeholder. */
export function removeTeamAvatar(client: ApiClient, slug: string, attendeeId: number): Promise<Attendee> {
    return client.delete<{ data: Attendee }>(`${eventPath(slug)}/attendees/${attendeeId}/avatar`)
        .then((response) => response.data);
}

/**
 * Enrol a team mate into the empty seat.
 *
 * They are named by address rather than by account, because most partners have
 * none when they are named: the API invites them, which is what creates one.
 */
export function addMember(
    client: ApiClient,
    slug: string,
    attendeeId: number,
    player: PlayerEntry,
): Promise<Attendee> {
    return client.post<{ data: Attendee }>(`${eventPath(slug)}/attendees/${attendeeId}/members`, {
        ...(player.name === undefined || player.name === null || player.name === '' ? {} : { name: player.name }),
        email: player.email,
        ...(player.faction_id === undefined || player.faction_id === null ? {} : { faction_id: player.faction_id }),
    }).then((response) => response.data);
}

/**
 * Correct a team mate's details on their behalf.
 *
 * Only while their invitation is outstanding: once they claim the account the
 * API refuses this, because their name and address are then theirs. Addressed
 * by membership because the seat is what is being amended — correcting the
 * address moves that seat to another account, faction and list intact.
 */
export function amendMember(
    client: ApiClient,
    slug: string,
    attendeeId: number,
    membershipId: number,
    changes: { name?: string; email?: string; faction_id?: number | null },
): Promise<Attendee> {
    return client.patch<{ data: Attendee }>(
        `${eventPath(slug)}/attendees/${attendeeId}/members/${membershipId}`,
        changes,
    ).then((response) => response.data);
}

/** A fresh invitation to the address already on file, for a partner who never answered. */
export function resendMemberInvite(
    client: ApiClient,
    slug: string,
    attendeeId: number,
    membershipId: number,
): Promise<unknown> {
    return client.post(`${eventPath(slug)}/attendees/${attendeeId}/members/${membershipId}/invite`, {});
}

/**
 * Take a Player out of a team.
 *
 * Addressed by the Player rather than the seat, as the API's route is. Their
 * account is left alone; only their place in this team goes.
 */
export function removeMember(client: ApiClient, slug: string, attendeeId: number, userId: number): Promise<void> {
    return client.delete<null>(`${eventPath(slug)}/attendees/${attendeeId}/members/${userId}`).then(() => undefined);
}

function eventPath(slug: string): string {
    return `/api/events/${encodeURIComponent(slug)}`;
}
