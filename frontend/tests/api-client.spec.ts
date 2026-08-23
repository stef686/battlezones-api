import { afterEach, describe, expect, it, vi } from 'vitest';

import { ApiClient } from '@/api/client';
import { ApiError } from '@/api/errors';
import { InMemoryTokenStorage } from '@/api/token-storage';

interface StubResponse {
    status: number;
    body?: unknown;
    headers?: Record<string, string>;
}

function stubFetch(...responses: StubResponse[]) {
    const fetch = vi.fn();

    responses.forEach(({ status, body, headers }) => {
        fetch.mockResolvedValueOnce({
            ok: status >= 200 && status < 300,
            status,
            headers: new Headers(headers ?? {}),
            json: () => Promise.resolve(body ?? null),
        });
    });

    vi.stubGlobal('fetch', fetch);

    return fetch;
}

function clientWith(token: string | null, options: Partial<ConstructorParameters<typeof ApiClient>[0]> = {}) {
    const storage = new InMemoryTokenStorage();

    if (token !== null) {
        storage.write(token);
    }

    return { storage, client: new ApiClient({ baseUrl: 'https://api.test', storage, ...options }) };
}

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('authentication', () => {
    it('sends the bearer token when there is one', async () => {
        const fetch = stubFetch({ status: 200, body: { data: null } });
        const { client } = clientWith('a-token');

        await client.get('/api/profile');

        expect(fetch).toHaveBeenCalledWith('https://api.test/api/profile', expect.objectContaining({
            headers: expect.objectContaining({ Authorization: 'Bearer a-token' }),
        }));
    });

    it('sends no authorization header when there is no token', async () => {
        const fetch = stubFetch({ status: 200, body: { data: [] } });
        const { client } = clientWith(null);

        await client.get('/api/events');

        const [, init] = fetch.mock.calls[0]!;
        expect(init?.headers).not.toHaveProperty('Authorization');
    });

    it('stores the session returned by login', async () => {
        stubFetch({ status: 200, body: { token: 'fresh', expires_at: '2026-10-01T00:00:00Z' } });
        const { client, storage } = clientWith(null);

        await client.login('ada@example.com', 'password', 'iPhone');

        expect(storage.read()).toBe('fresh');
        expect(client.isAuthenticated()).toBe(true);
    });
});

describe('token refresh', () => {
    it('refreshes once for concurrent 401s rather than racing itself', async () => {
        const fetch = stubFetch(
            { status: 401, body: { message: 'Unauthenticated.' } },
            { status: 401, body: { message: 'Unauthenticated.' } },
            { status: 401, body: { message: 'Unauthenticated.' } },
            { status: 200, body: { token: 'rotated', expires_at: null } },
            { status: 200, body: { data: 'one' } },
            { status: 200, body: { data: 'two' } },
            { status: 200, body: { data: 'three' } },
        );
        const { client, storage } = clientWith('stale');

        await Promise.all([
            client.get('/api/one'),
            client.get('/api/two'),
            client.get('/api/three'),
        ]);

        const refreshCalls = fetch.mock.calls.filter(([url]) => String(url).endsWith('/api/auth/refresh'));

        expect(refreshCalls).toHaveLength(1);
        expect(storage.read()).toBe('rotated');
    });

    it('retries the original request with the rotated token', async () => {
        const fetch = stubFetch(
            { status: 401, body: { message: 'Unauthenticated.' } },
            { status: 200, body: { token: 'rotated', expires_at: null } },
            { status: 200, body: { data: 'my game' } },
        );
        const { client } = clientWith('stale');

        await expect(client.get('/api/events/x/my-game')).resolves.toEqual({ data: 'my game' });

        const [lastUrl, lastInit] = fetch.mock.calls.at(-1)!;
        expect(lastUrl).toBe('https://api.test/api/events/x/my-game');
        expect((lastInit as RequestInit).headers).toMatchObject({ Authorization: 'Bearer rotated' });
    });

    it('clears the session and says so when the refresh itself is rejected', async () => {
        stubFetch(
            { status: 401, body: { message: 'Unauthenticated.' } },
            { status: 401, body: { message: 'Unauthenticated.' } },
        );
        const onSessionLost = vi.fn();
        const { client, storage } = clientWith('expired', { onSessionLost });

        await expect(client.get('/api/profile')).rejects.toMatchObject({ kind: 'unauthenticated' });

        expect(storage.read()).toBeNull();
        expect(onSessionLost).toHaveBeenCalledTimes(1);
    });

    it('refreshes proactively when the token is close to expiring', async () => {
        const fetch = stubFetch({ status: 200, body: { token: 'rotated', expires_at: null } });
        const { client, storage } = clientWith('nearly-stale');

        client.setSession({ token: 'nearly-stale', expiresAt: new Date(Date.now() + 30_000).toISOString() });
        await client.refreshIfDue();

        expect(fetch).toHaveBeenCalledTimes(1);
        expect(storage.read()).toBe('rotated');
    });

    it('leaves a token with life left in it alone', async () => {
        const fetch = stubFetch();
        const { client } = clientWith('fresh');

        client.setSession({ token: 'fresh', expiresAt: new Date(Date.now() + 3_600_000).toISOString() });
        await client.refreshIfDue();

        expect(fetch).not.toHaveBeenCalled();
    });

    it('has nothing to refresh when nobody is signed in', async () => {
        const fetch = stubFetch();
        const { client } = clientWith(null);

        await client.refreshIfDue();

        expect(fetch).not.toHaveBeenCalled();
    });
});

describe('error normalisation', () => {
    it('maps validation errors to fields, keeping dot paths whole', async () => {
        stubFetch({
            status: 422,
            body: {
                message: 'The given data was invalid.',
                errors: { 'scores.9.victory-points': ['The victory points must be a number.'] },
            },
        });
        const { client } = clientWith('a-token');

        const error = (await client.post('/api/events/x/games/1/result').catch((e: unknown) => e)) as ApiError;

        expect(error).toBeInstanceOf(ApiError);
        expect(error.kind).toBe('validation');
        expect(error.fields['scores.9.victory-points']).toEqual(['The victory points must be a number.']);
    });

    it('never logs the reader out on a 403', async () => {
        stubFetch({ status: 403, body: { message: 'This action is unauthorized.' } });
        const onSessionLost = vi.fn();
        const { client, storage } = clientWith('a-token', { onSessionLost });

        await expect(client.get('/api/events/x/flags')).rejects.toMatchObject({ kind: 'forbidden' });

        expect(storage.read()).toBe('a-token');
        expect(onSessionLost).not.toHaveBeenCalled();
    });

    it('does not describe a 404 as private, since it cannot know that', async () => {
        stubFetch({ status: 404, body: {} });
        const { client } = clientWith('a-token');

        const error = (await client.get('/api/events/nope').catch((e: unknown) => e)) as ApiError;

        expect(error.kind).toBe('not_found');
        expect(error.message).toBe('That could not be found.');
        expect(error.message.toLowerCase()).not.toContain('private');
    });

    it('reads the retry header on a 429 so a caller can back off honestly', async () => {
        stubFetch({ status: 429, body: { message: 'Too Many Attempts.' }, headers: { 'Retry-After': '43' } });
        const { client } = clientWith('a-token');

        const error = (await client.post('/api/login/token').catch((e: unknown) => e)) as ApiError;

        expect(error.kind).toBe('rate_limited');
        expect(error.retryAfter).toBe(43);
    });

    it('tells a lost request apart from a rejected one', async () => {
        vi.stubGlobal('fetch', vi.fn().mockRejectedValue(new TypeError('Failed to fetch')));
        const { client } = clientWith('a-token');

        const error = (await client.get('/api/profile').catch((e: unknown) => e)) as ApiError;

        expect(error.kind).toBe('network');
        expect(error.status).toBeNull();
    });

    it('keeps the conflict body, so a caller can recognise its own submission', async () => {
        stubFetch({
            status: 409,
            body: {
                message: 'A result has already been submitted for this game.',
                data: { id: 18, result: { submitted_by: { id: 12, name: 'Ada Lovelace' } } },
            },
        });
        const { client } = clientWith('a-token');

        const error = (await client.post('/api/events/x/games/18/result').catch((e: unknown) => e)) as ApiError;

        expect(error.kind).toBe('conflict');
        expect(error.body).toMatchObject({ data: { id: 18 } });
    });
});
