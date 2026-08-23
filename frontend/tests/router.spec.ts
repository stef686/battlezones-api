import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { createAppRouter } from '@/router';

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

beforeEach(() => {
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
