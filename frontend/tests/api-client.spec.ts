import { afterEach, describe, expect, it, vi } from 'vitest';

import { ApiClient, ApiError } from '@/api/client';

function respondWith(status: number, body: unknown): void {
    vi.stubGlobal('fetch', vi.fn().mockResolvedValue({
        ok: status >= 200 && status < 300,
        status,
        json: () => Promise.resolve(body),
    }));
}

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('ApiClient', () => {
    it('sends the bearer token when there is one', async () => {
        respondWith(200, { data: null });

        await new ApiClient(() => 'a-token', 'https://api.test').get('/api/profile');

        expect(fetch).toHaveBeenCalledWith('https://api.test/api/profile', expect.objectContaining({
            headers: expect.objectContaining({ Authorization: 'Bearer a-token' }),
        }));
    });

    it('sends no authorization header when there is no token', async () => {
        respondWith(200, { data: null });

        await new ApiClient(() => null, 'https://api.test').get('/api/events');

        const [, init] = vi.mocked(fetch).mock.calls[0]!;
        expect(init?.headers).not.toHaveProperty('Authorization');
    });

    it('throws an ApiError carrying the status and body so callers can branch', async () => {
        respondWith(409, { message: 'A result has already been submitted for this game.' });

        const request = new ApiClient(() => 't', 'https://api.test').post('/api/events/a/games/1/result');

        await expect(request).rejects.toBeInstanceOf(ApiError);
        await expect(request).rejects.toMatchObject({ status: 409 });
    });
});
