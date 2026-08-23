import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { createAppRouter } from '@/router';
import { useSessionStore } from '@/stores/session';

function routerWithSession(token: string | null) {
    const router = createAppRouter();
    const storage = new InMemoryTokenStorage();

    if (token !== null) {
        storage.write(token);
    }

    // createApiClient owns the module-level client the guard reads.
    const client = createApiClient(router, { baseUrl: 'https://api.test', storage });

    return { router, client, storage };
}

/** A loaded profile, which is what the guard reads to know the account's state. */
function signedInAs(isClaimed: boolean, invite: { token: string; eventSlug: string } | null) {
    const session = useSessionStore();

    session.viewer = {
        id: 12,
        public_name: 'Ada Lovelace',
        is_claimed: isClaimed,
        email_verified: true,
        unread_notifications_count: 0,
    };

    if (invite !== null) {
        session.rememberInvite(invite);
    }

    return session;
}

beforeEach(() => {
    window.localStorage.clear();
    setActivePinia(createPinia());
    vi.stubGlobal('fetch', vi.fn().mockResolvedValue({
        ok: true,
        status: 200,
        headers: new Headers(),
        json: () => Promise.resolve({ data: null }),
    }));
});

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('route guards', () => {
    it('sends an unauthenticated reader to login, remembering where they were going', async () => {
        const { router } = routerWithSession(null);

        await router.push('/events/open/my-game');
        await router.isReady();

        expect(router.currentRoute.value.name).toBe('login');
        expect(router.currentRoute.value.query.redirect).toBe('/events/open/my-game');
    });

    it('lets an authenticated reader through', async () => {
        const { router } = routerWithSession('a-token');

        await router.push('/events/open/my-game');
        await router.isReady();

        expect(router.currentRoute.value.name).toBe('my-game');
    });

    it('leaves public routes open, so standings can be read without an account', async () => {
        const { router } = routerWithSession(null);

        await router.push('/events/open/standings');
        await router.isReady();

        expect(router.currentRoute.value.name).toBe('standings');
    });

    it('sends a reader whose session dies back to login, keeping their route', async () => {
        const { router, client } = routerWithSession('a-token');

        await router.push('/events/open/my-game');
        await router.isReady();

        vi.stubGlobal('fetch', vi.fn().mockResolvedValue({
            ok: false,
            status: 401,
            headers: new Headers(),
            json: () => Promise.resolve({ message: 'Unauthenticated.' }),
        }));

        await client.get('/api/events/open/my-game').catch(() => null);

        await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('login'));
        expect(router.currentRoute.value.query.redirect).toBe('/events/open/my-game');
    });
});

describe('restricted mode for an unclaimed account', () => {
    const INVITE = { token: 'plain-token', eventSlug: 'open' };

    it('lets an unclaimed session browse the Event it was invited to', async () => {
        const { router } = routerWithSession('invited');
        signedInAs(false, INVITE);

        await router.push('/events/open/my-game');
        await router.isReady();

        expect(router.currentRoute.value.name).toBe('my-game');
    });

    it('sends an unclaimed session anywhere else to Claim', async () => {
        const { router } = routerWithSession('invited');
        signedInAs(false, INVITE);

        await router.push('/events/another-event/standings');
        await router.isReady();

        expect(router.currentRoute.value.name).toBe('claim');
    });

    it('keeps an unclaimed session off surfaces that belong to no Event', async () => {
        const { router } = routerWithSession('invited');
        signedInAs(false, INVITE);

        await router.push('/');
        await router.isReady();

        expect(router.currentRoute.value.name).toBe('claim');
    });

    it('leaves the way out of restriction open', async () => {
        const { router } = routerWithSession('invited');
        signedInAs(false, INVITE);

        await router.push('/claim');
        await router.isReady();

        expect(router.currentRoute.value.name).toBe('claim');
    });

    it('restricts nothing once the account is claimed', async () => {
        const { router } = routerWithSession('a-token');
        signedInAs(true, null);

        await router.push('/events/another-event/standings');
        await router.isReady();

        expect(router.currentRoute.value.name).toBe('standings');
    });

    it('restricts nothing while the profile is unknown, because it cannot know it should', async () => {
        const { router } = routerWithSession('a-token');

        await router.push('/events/another-event/standings');
        await router.isReady();

        expect(router.currentRoute.value.name).toBe('standings');
    });
});

describe('invitation routes', () => {
    it('opens an invitation without a session, so it can be read before it is trusted', async () => {
        const { router } = routerWithSession(null);

        await router.push('/invites/plain-token');
        await router.isReady();

        expect(router.currentRoute.value.name).toBe('invite');
        expect(router.currentRoute.value.params.token).toBe('plain-token');
    });

    it('opens the reset link without a session, since a forgotten password is why it exists', async () => {
        const { router } = routerWithSession(null);

        await router.push('/reset-password?token=abc&email=ada%40example.com');
        await router.isReady();

        expect(router.currentRoute.value.name).toBe('reset-password');
        expect(router.currentRoute.value.query.email).toBe('ada@example.com');
    });
});
