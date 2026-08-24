import type { ApiClient, Session } from './client';

export interface InviteEvent {
    slug: string;
    name: string;
    starts_at: string | null;
    ends_at: string | null;
}

/**
 * What an invitation link resolves to, before anyone has proved anything.
 *
 * Read without a session on purpose: the reader has to be able to tell the
 * invitation is genuine — the right Event, their own address — before they
 * hand it a password.
 */
export interface Invite {
    id: number;
    role: string;
    email: string;
    name: string | null;
    is_claimed: boolean;
    attendee_id: number | null;
    event: InviteEvent;
    expires_at: string;
    revoked_at: string | null;
}

export interface ClaimDetails {
    password: string;
    passwordConfirmation: string;
    deviceName: string;
    /** The name to go by, when it differs from the one the Organiser entered. */
    name?: string | null;
}

interface TokenResponse {
    token: string;
    expires_at?: string | null;
}

export function fetchInvite(client: ApiClient, token: string): Promise<Invite> {
    return client.get<{ data: Invite }>(invitePath(token)).then((response) => response.data);
}

/**
 * Enter with the Invite alone.
 *
 * No password: a Captain forwarding their invitation to a partner in a pub is
 * the case this exists for, and a password demanded at the door loses people.
 * The session it returns expires with the Invite, and the API refuses to
 * refresh it past that.
 */
export async function enterWithInvite(client: ApiClient, token: string, deviceName: string): Promise<Session> {
    const response = await client.post<TokenResponse>(`${invitePath(token)}/session`, { device_name: deviceName });

    return sessionFrom(client, response);
}

/**
 * Set a password, turning the invited account into one its owner keeps.
 *
 * This revokes the Invite, so the session it returns replaces the one the
 * Invite granted rather than sitting beside it.
 */
export async function claimInvite(client: ApiClient, token: string, details: ClaimDetails): Promise<Session> {
    const response = await client.post<TokenResponse>(`${invitePath(token)}/claim`, {
        password: details.password,
        password_confirmation: details.passwordConfirmation,
        device_name: details.deviceName,
        ...(details.name === undefined || details.name === null || details.name === '' ? {} : { name: details.name }),
    });

    return sessionFrom(client, response);
}

function invitePath(token: string): string {
    return `/api/invites/${encodeURIComponent(token)}`;
}

function sessionFrom(client: ApiClient, response: TokenResponse): Session {
    const session = { token: response.token, expiresAt: response.expires_at ?? null };

    client.setSession(session);

    return session;
}
