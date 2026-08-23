import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import { ApiClient } from '@/api/client';
import { ApiError } from '@/api/errors';
import { BrowserInviteStorage } from '@/api/invite-storage';
import { claimInvite, enterWithInvite, fetchInvite } from '@/api/invites';
import { InMemoryTokenStorage } from '@/api/token-storage';

const INVITE = {
    id: 7,
    role: 'captain',
    email: 'captain@example.com',
    name: 'Ada Lovelace',
    is_claimed: false,
    attendee_id: null,
    event: {
        slug: 'london-grand-tournament',
        name: 'London Grand Tournament',
        starts_at: '2026-09-12T09:00:00+00:00',
        ends_at: '2026-09-13T18:00:00+00:00',
    },
    expires_at: '2026-09-13T18:00:00+00:00',
    revoked_at: null,
};

interface StubResponse {
    status: number;
    body?: unknown;
}

function stubFetch(...responses: StubResponse[]) {
    const fetch = vi.fn();

    responses.forEach(({ status, body }) => {
        fetch.mockResolvedValueOnce({
            ok: status >= 200 && status < 300,
            status,
            headers: new Headers(),
            json: () => Promise.resolve(body ?? null),
        });
    });

    vi.stubGlobal('fetch', fetch);

    return fetch;
}

function client(token: string | null = null) {
    const storage = new InMemoryTokenStorage();

    if (token !== null) {
        storage.write(token);
    }

    return { storage, client: new ApiClient({ baseUrl: 'https://api.test', storage }) };
}

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('reading an invitation', () => {
    it('resolves the token without asking the reader for anything', async () => {
        const fetch = stubFetch({ status: 200, body: { data: INVITE } });
        const { client: api } = client();

        await expect(fetchInvite(api, 'plain-token')).resolves.toMatchObject({
            email: 'captain@example.com',
            event: { name: 'London Grand Tournament' },
        });

        const [url, init] = fetch.mock.calls[0]!;
        expect(url).toBe('https://api.test/api/invites/plain-token');
        expect((init as RequestInit).headers).not.toHaveProperty('Authorization');
    });

    it('escapes a token so a stray character cannot rewrite the path', async () => {
        const fetch = stubFetch({ status: 200, body: { data: INVITE } });
        const { client: api } = client();

        await fetchInvite(api, 'a/../../profile');

        expect(fetch.mock.calls[0]![0]).toBe('https://api.test/api/invites/a%2F..%2F..%2Fprofile');
    });

    it('reports an expired invitation as gone, in the API\'s own words', async () => {
        stubFetch({
            status: 410,
            body: { message: 'This invitation has expired. Log in, or ask an organiser for a new one.', code: 'invite_expired' },
        });
        const { client: api } = client();

        const error = (await fetchInvite(api, 'stale').catch((e: unknown) => e)) as ApiError;

        expect(error.kind).toBe('gone');
        expect(error.message).toContain('expired');
    });

    it('reports an unknown token as missing rather than as a server fault', async () => {
        stubFetch({ status: 404, body: { message: 'This invitation link is not valid.', code: 'invite_not_found' } });
        const { client: api } = client();

        const error = (await fetchInvite(api, 'nope').catch((e: unknown) => e)) as ApiError;

        expect(error.kind).toBe('not_found');
    });
});

describe('entering with an invitation', () => {
    it('exchanges the token for a session without a password', async () => {
        const fetch = stubFetch({ status: 200, body: { token: 'invited', expires_at: '2026-09-13T18:00:00Z' } });
        const { client: api, storage } = client();

        const session = await enterWithInvite(api, 'plain-token', 'iPhone');

        expect(storage.read()).toBe('invited');
        expect(session.expiresAt).toBe('2026-09-13T18:00:00Z');

        const [url, init] = fetch.mock.calls[0]!;
        expect(url).toBe('https://api.test/api/invites/plain-token/session');
        expect(JSON.parse((init as RequestInit).body as string)).toEqual({ device_name: 'iPhone' });
    });

    it('keeps the invitation\'s expiry, so the session dies when the invitation does', async () => {
        const expiresAt = new Date(Date.now() + 30_000).toISOString();
        stubFetch(
            { status: 200, body: { token: 'invited', expires_at: expiresAt } },
            { status: 200, body: { token: 'rotated', expires_at: expiresAt } },
        );
        const { client: api } = client();

        await enterWithInvite(api, 'plain-token', 'iPhone');

        // Within the refresh margin: the client knows the session is nearly up
        // rather than discovering it on the next request.
        await api.refreshIfDue();

        expect(api.token()).toBe('rotated');
    });
});

describe('claiming an invited account', () => {
    it('sends the password and its confirmation the way the API names them', async () => {
        const fetch = stubFetch({ status: 201, body: { token: 'claimed', expires_at: null } });
        const { client: api, storage } = client('invited');

        await claimInvite(api, 'plain-token', {
            password: 'a-good-password',
            passwordConfirmation: 'a-good-password',
            deviceName: 'iPhone',
        });

        const [url, init] = fetch.mock.calls[0]!;
        expect(url).toBe('https://api.test/api/invites/plain-token/claim');
        expect(JSON.parse((init as RequestInit).body as string)).toEqual({
            password: 'a-good-password',
            password_confirmation: 'a-good-password',
            device_name: 'iPhone',
        });

        // The claim replaces the invited session rather than sitting beside it.
        expect(storage.read()).toBe('claimed');
    });

    it('omits a name nobody changed, rather than sending an empty one', async () => {
        const fetch = stubFetch({ status: 201, body: { token: 'claimed' } });
        const { client: api } = client('invited');

        await claimInvite(api, 'plain-token', {
            password: 'a-good-password',
            passwordConfirmation: 'a-good-password',
            deviceName: 'iPhone',
            name: '',
        });

        expect(JSON.parse((fetch.mock.calls[0]![1] as RequestInit).body as string)).not.toHaveProperty('name');
    });

    it('surfaces a mismatched confirmation as a field error', async () => {
        stubFetch({
            status: 422,
            body: { message: 'The given data was invalid.', errors: { password: ['The password field confirmation does not match.'] } },
        });
        const { client: api } = client('invited');

        const error = (await claimInvite(api, 'plain-token', {
            password: 'a-good-password',
            passwordConfirmation: 'mistyped',
            deviceName: 'iPhone',
        }).catch((e: unknown) => e)) as ApiError;

        expect(error.kind).toBe('validation');
        expect(error.fields.password).toHaveLength(1);
    });
});

describe('remembering the invitation', () => {
    const storage = new BrowserInviteStorage();

    beforeEach(() => {
        window.localStorage.clear();
    });

    it('survives a reload, because claiming needs the token the link carried', () => {
        storage.write({ token: 'plain-token', eventSlug: 'london-grand-tournament' });

        expect(new BrowserInviteStorage().read()).toEqual({
            token: 'plain-token',
            eventSlug: 'london-grand-tournament',
        });
    });

    it('treats an unreadable entry as no invitation at all', () => {
        window.localStorage.setItem('battlezones.invite', 'not json');

        expect(storage.read()).toBeNull();
    });

    it('treats a half-written entry as no invitation at all', () => {
        window.localStorage.setItem('battlezones.invite', JSON.stringify({ token: 'plain-token' }));

        expect(storage.read()).toBeNull();
    });

    it('forgets it on request, so a claimed account is not still confined', () => {
        storage.write({ token: 'plain-token', eventSlug: 'london-grand-tournament' });
        storage.clear();

        expect(storage.read()).toBeNull();
    });
});
