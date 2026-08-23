/**
 * The Invite an unclaimed session came in on.
 *
 * Claiming needs the invite token, and the only place it exists is the link
 * the Player followed. If it is forgotten on the first reload, a Player who
 * entered without a password can never set one — so it is remembered
 * alongside the access token, and forgotten the moment the account is claimed.
 *
 * The Event slug rides along because the route guard has to know which Event
 * an unclaimed session is confined to before any request has been made.
 */
export interface RememberedInvite {
    token: string;
    eventSlug: string;
}

export interface InviteStorage {
    read(): RememberedInvite | null;
    write(invite: RememberedInvite): void;
    clear(): void;
}

const STORAGE_KEY = 'battlezones.invite';

/**
 * localStorage, defensively, for the same reasons as the token: a private
 * window makes even reading it throw, and the app must still start.
 */
export class BrowserInviteStorage implements InviteStorage {
    read(): RememberedInvite | null {
        try {
            return parse(window.localStorage.getItem(STORAGE_KEY));
        } catch {
            return null;
        }
    }

    write(invite: RememberedInvite): void {
        try {
            window.localStorage.setItem(STORAGE_KEY, JSON.stringify(invite));
        } catch {
            // An invite that cannot outlive the tab is still usable in it.
        }
    }

    clear(): void {
        try {
            window.localStorage.removeItem(STORAGE_KEY);
        } catch {
            // Nothing to clear if the store was never readable.
        }
    }
}

/**
 * Anything unrecognisable is treated as nothing at all: a half-written or
 * stale entry should send the reader back to their invitation link, not crash
 * the screen that reads it.
 */
function parse(raw: string | null): RememberedInvite | null {
    if (raw === null) {
        return null;
    }

    const parsed: unknown = JSON.parse(raw);

    if (typeof parsed !== 'object' || parsed === null) {
        return null;
    }

    const { token, eventSlug } = parsed as Partial<RememberedInvite>;

    if (typeof token !== 'string' || token === '' || typeof eventSlug !== 'string' || eventSlug === '') {
        return null;
    }

    return { token, eventSlug };
}
