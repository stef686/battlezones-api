import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { createAppRouter } from '@/router';
import FlagsView from '@/views/FlagsView.vue';

const EVENT_SLUG = 'london-grand-tournament';

function eventBody(organise = true) {
    return {
        data: {
            id: 1,
            name: 'London Grand Tournament',
            slug: EVENT_SLUG,
            description: null,
            status: 'active',
            starts_at: null,
            ends_at: null,
            max_attendees: null,
            attendee_size: 2,
            requires_allegiance: true,
            registration_closes_at: null,
            is_full: false,
            game_system: null,
            venue: { name: null, address: null, city: null, country: null },
            documents: [],
            viewer: {
                is_organiser: organise,
                is_lead_organiser: organise,
                is_attendee: false,
                attendee_id: null,
                permissions: { organise, register: false, manage_organisers: organise },
            },
        },
    };
}

function flag(overrides: Record<string, unknown> = {}) {
    return {
        id: 3,
        game_id: 18,
        reason: 'We agreed 85-70 and it went in the other way round.',
        flagged_at: '2026-09-12T14:20:00Z',
        flagged_by: { id: 12, name: 'Ada Lovelace' },
        game: {
            id: 18,
            table_number: 5,
            is_bye: false,
            round: { id: 4, number: 2, name: 'Round 2' },
            attendees: [
                { id: 9, name: 'Sons of Terra', scores: { 'victory-points': '70.00', 'match-points': '0.00' } },
                { id: 10, name: 'The Warmaster\'s Own', scores: { 'victory-points': '85.00', 'match-points': '3.00' } },
            ],
        },
        resolved_at: null,
        ...overrides,
    };
}

const NOT_FOUND = { status: 404, body: { message: 'Not Found.' } };

function stubApi(routes: Record<string, { status: number; body?: unknown }>) {
    const fetch = vi.fn((url: string, init?: RequestInit) => {
        void init;

        const path = String(url).replace('https://api.test', '').split('?')[0] ?? '';
        const match = Object.entries(routes).find(([pattern]) => path.endsWith(pattern));
        const { status, body } = match?.[1] ?? NOT_FOUND;

        return Promise.resolve({
            ok: status >= 200 && status < 300,
            status,
            headers: new Headers(),
            json: () => Promise.resolve(body ?? null),
        });
    });

    vi.stubGlobal('fetch', fetch);

    return fetch;
}

let router: Router;
let pinia: ReturnType<typeof createPinia>;
let queryClient: QueryClient;

function mountView() {
    return mount(FlagsView as never, ({
        props: { eventSlug: EVENT_SLUG },
        global: { plugins: [pinia, router, [VueQueryPlugin, { queryClient }]] },
    }) as never);
}

beforeEach(async () => {
    window.localStorage.clear();
    pinia = createPinia();
    setActivePinia(pinia);

    queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });

    router = createAppRouter();

    const storage = new InMemoryTokenStorage();
    storage.write('a-token');
    createApiClient(router, { baseUrl: 'https://api.test', storage });

    await router.push(`/events/${EVENT_SLUG}/organise/flags`);
    await router.isReady();
});

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('the queue of flagged results', () => {
    it('shows each disputed game, its scores, and what the Player said about it', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/flags`]: { status: 200, body: { data: [flag()] } },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView();
        await flushPromises();

        const queued = view.get('[data-testid="flag-3"]');

        expect(queued.text()).toContain('Round 2');
        expect(queued.text()).toContain('Table 5');
        expect(queued.text()).toContain('We agreed 85-70');
        expect(queued.text()).toContain('Ada Lovelace');

        // The current scores, because an Organiser is deciding what to change
        // them to rather than looking them up somewhere else.
        expect((queued.get('[data-testid="flag-score-9"]').element as HTMLInputElement).value).toBe('70');
        expect((queued.get('[data-testid="flag-score-10"]').element as HTMLInputElement).value).toBe('85');
    });

    it('corrects the scores and closes the flag in one move', async () => {
        const fetch = stubApi({
            [`/api/events/${EVENT_SLUG}/flags`]: { status: 200, body: { data: [flag()] } },
            [`/api/events/${EVENT_SLUG}/games/18/result`]: { status: 200, body: { data: {} } },
            [`/api/events/${EVENT_SLUG}/games/18/flag/resolve`]: { status: 200, body: { data: {} } },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="flag-score-9"]').setValue('85');
        await view.get('[data-testid="flag-score-10"]').setValue('70');
        await view.get('[data-testid="correct-flag-3"]').trigger('click');
        await flushPromises();

        const corrected = fetch.mock.calls.find(([url]) => String(url).endsWith('/games/18/result'))!;
        expect(corrected[1]?.method).toBe('PUT');
        expect(JSON.parse(corrected[1]?.body as string)).toEqual({
            scores: { 9: { 'victory-points': 85 }, 10: { 'victory-points': 70 } },
        });

        // Correcting is the resolution: an Organiser who has fixed the score
        // should not have to remember to close the flag as well.
        const resolved = fetch.mock.calls.find(([url]) => String(url).endsWith('/games/18/flag/resolve'))!;
        expect(resolved[1]?.method).toBe('POST');
    });

    it('clears a flag without touching the scores, for a result that turned out to be right', async () => {
        const fetch = stubApi({
            [`/api/events/${EVENT_SLUG}/flags`]: { status: 200, body: { data: [flag()] } },
            [`/api/events/${EVENT_SLUG}/games/18/flag/resolve`]: { status: 200, body: { data: {} } },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="dismiss-flag-3"]').trigger('click');
        await flushPromises();

        expect(fetch.mock.calls.some(([url]) => String(url).endsWith('/games/18/result'))).toBe(false);
        expect(fetch.mock.calls.some(([url]) => String(url).endsWith('/games/18/flag/resolve'))).toBe(true);
    });

    it('takes a resolved flag out of the queue', async () => {
        let open = [flag()];

        vi.stubGlobal('fetch', vi.fn((url: string, init?: RequestInit) => {
            const path = String(url).replace('https://api.test', '').split('?')[0] ?? '';

            if (path.endsWith('/flag/resolve')) {
                open = [];

                return Promise.resolve({ ok: true, status: 200, headers: new Headers(), json: () => Promise.resolve({ data: {} }) });
            }

            void init;

            const body = path.endsWith('/flags') ? { data: open } : eventBody();

            return Promise.resolve({ ok: true, status: 200, headers: new Headers(), json: () => Promise.resolve(body) });
        }));

        const view = mountView();
        await flushPromises();

        expect(view.find('[data-testid="flag-3"]').exists()).toBe(true);

        await view.get('[data-testid="dismiss-flag-3"]').trigger('click');
        await flushPromises();
        await flushPromises();

        expect(view.find('[data-testid="flag-3"]').exists()).toBe(false);
        expect(view.find('[data-testid="no-flags"]').exists()).toBe(true);
    });

    it('says so when nothing is disputed, rather than showing an empty screen', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/flags`]: { status: 200, body: { data: [] } },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="no-flags"]').text()).toContain('Nothing');
    });

    it('is not a screen for someone who does not run the event', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody(false) },
        });

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="missing"]').text()).toContain('could not find');
        expect(view.find('[data-testid="flag-3"]').exists()).toBe(false);
    });
});
