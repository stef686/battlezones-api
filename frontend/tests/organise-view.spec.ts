import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { createAppRouter } from '@/router';
import OrganiseView from '@/views/OrganiseView.vue';

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

function pairing(overrides: Record<string, unknown> = {}) {
    return {
        id: 18,
        table_number: 1,
        is_bye: false,
        is_rematch: false,
        result: { submitted_at: null, is_flagged: false },
        attendees: [
            { id: 9, name: 'Sons of Terra', allegiance: 'loyalist', members: [], scores: {} },
            { id: 10, name: 'The Warmaster\'s Own', allegiance: 'traitor', members: [], scores: {} },
        ],
        ...overrides,
    };
}

const STANDINGS = {
    data: [
        { id: 1, position: 1, attendee: { id: 9, name: 'Sons of Terra' }, scores: [] },
        { id: 2, position: 4, attendee: { id: 10, name: 'The Warmaster\'s Own' }, scores: [] },
    ],
};

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
    return mount(OrganiseView as never, ({
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

    await router.push(`/events/${EVENT_SLUG}/organise`);
    await router.isReady();
});

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('who may run an event', () => {
    it('points at the event settings without putting them on the round-running screen', async () => {
        stubApi({ [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() } });

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="settings-link"]').attributes('href'))
            .toBe(`/events/${EVENT_SLUG}/organise/settings`);
    });

    it('is not there at all for a reader without the permission', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody(false) },
        });

        const view = mountView();
        await flushPromises();

        // The same answer as an Event that does not exist: whether someone
        // else's Event has an organiser screen is not this reader's business.
        expect(view.find('[data-testid="missing"]').exists()).toBe(true);
        expect(view.find('[data-testid="settings-link"]').exists()).toBe(false);
        expect(view.find('[data-testid="rounds-link"]').exists()).toBe(false);
    });

    it('shows the hub to an organiser', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: { data: [] } },
            [`/api/events/${EVENT_SLUG}/standings`]: { status: 200, body: { data: [] } },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: { data: { current_round: null, rounds: null, standings: null, polls: null } } },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView();
        await flushPromises();

        expect(view.find('[data-testid="missing"]').exists()).toBe(false);
        expect(view.find('[data-testid="rounds-link"]').exists()).toBe(true);
    });
});

describe('what is holding up the next round', () => {
    const LIVE_ROUNDS = { data: [{ id: 3, number: 1, name: 'Round 1', status: 'live' }] };

    function withLiveRound(games: unknown[]) {
        return {
            [`/api/events/${EVENT_SLUG}/rounds/3`]: {
                status: 200,
                body: { data: { id: 3, number: 1, name: 'Round 1', status: 'live', games } },
            },
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: LIVE_ROUNDS },
            [`/api/events/${EVENT_SLUG}/standings`]: { status: 200, body: STANDINGS },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: { data: { current_round: { id: 3, number: 1 }, rounds: 'a', standings: null, polls: null } } },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        };
    }

    it('says nothing is being played before the first round is published', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: { data: [] } },
            [`/api/events/${EVENT_SLUG}/standings`]: { status: 200, body: { data: [] } },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: { data: { current_round: null, rounds: null, standings: null, polls: null } } },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="rounds-link"]').text()).toContain('Nothing being played');
    });

    it('counts the tables still playing in the rounds row', async () => {
        stubApi(withLiveRound([
            pairing({ id: 18, table_number: 1, result: { submitted_at: '2026-09-12T12:00:00Z', is_flagged: false } }),
            pairing({ id: 19, table_number: 5 }),
            pairing({ id: 20, table_number: 6 }),
        ]));

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="rounds-link"]').text()).toContain('Round 1 \u00b7 2 to go');
    });

    it('never counts a bye, since nobody can report one', async () => {
        stubApi(withLiveRound([
            pairing({ id: 18, table_number: 1, result: { submitted_at: '2026-09-12T12:00:00Z', is_flagged: false } }),
            pairing({ id: 21, table_number: null, is_bye: true, attendees: [] }),
        ]));

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="rounds-link"]').text()).toContain('all reported');
    });

    it('says so when every table has reported', async () => {
        stubApi(withLiveRound([
            pairing({ result: { submitted_at: '2026-09-12T12:00:00Z', is_flagged: false } }),
        ]));

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="rounds-link"]').text()).toContain('Round 1 \u00b7 all reported');
    });
});

describe('scoring a bye', () => {
    function withBye() {
        const games = [
            pairing({ id: 18, table_number: 1, result: { submitted_at: '2026-09-12T12:00:00Z', is_flagged: false } }),
            pairing({
                id: 21,
                table_number: null,
                is_bye: true,
                attendees: [{ id: 11, name: 'Odd One Out', allegiance: 'loyalist', members: [], scores: {} }],
            }),
        ];

        return {
            [`/api/events/${EVENT_SLUG}/rounds/3`]: {
                status: 200,
                body: { data: { id: 3, number: 1, name: 'Round 1', status: 'live', games } },
            },
            [`/api/events/${EVENT_SLUG}/rounds`]: {
                status: 200,
                body: { data: [{ id: 3, number: 1, name: 'Round 1', status: 'live' }] },
            },
            [`/api/events/${EVENT_SLUG}/standings`]: { status: 200, body: STANDINGS },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: { data: { current_round: { id: 3, number: 1 }, rounds: 'a', standings: null, polls: null } } },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        };
    }

    it('offers the bye its own points entry, saying the win is already counted', async () => {
        stubApi(withBye());

        const view = mountView();
        await flushPromises();

        const bye = view.get('[data-testid="bye-21"]');

        expect(bye.text()).toContain('Odd One Out');
        expect(bye.text()).toContain('win');
    });

    it('sends the points for the bye Attendee alone', async () => {
        const fetch = stubApi({
            ...withBye(),
            [`/api/events/${EVENT_SLUG}/games/21/result`]: { status: 200, body: { data: {} } },
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="bye-score-21"]').setValue('60');
        await view.get('[data-testid="save-bye-21"]').trigger('click');
        await flushPromises();

        const saved = fetch.mock.calls.find(([url]) => String(url).endsWith('/games/21/result'))!;
        expect(saved[1]?.method).toBe('PUT');
        expect(JSON.parse(saved[1]?.body as string)).toEqual({ scores: { 11: { 'victory-points': 60 } } });
    });

    it('says the points were saved, so an Organiser knows before leaving the screen', async () => {
        stubApi({
            ...withBye(),
            [`/api/events/${EVENT_SLUG}/games/21/result`]: { status: 200, body: { data: {} } },
        });

        const view = mountView();
        await flushPromises();

        expect(view.find('[data-testid="bye-saved-21"]').exists()).toBe(false);

        await view.get('[data-testid="bye-score-21"]').setValue('60');
        await view.get('[data-testid="save-bye-21"]').trigger('click');
        await flushPromises();

        expect(view.get('[data-testid="bye-saved-21"]').text()).toContain('Saved');
    });
});

describe('disputed results from the organiser screen', () => {
    function withFlags(flags: unknown[]) {
        return {
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: { data: [] } },
            [`/api/events/${EVENT_SLUG}/standings`]: { status: 200, body: STANDINGS },
            [`/api/events/${EVENT_SLUG}/flags`]: { status: 200, body: { data: flags } },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: { data: { current_round: null, rounds: null, standings: null, polls: null } } },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        };
    }

    it('says how many results are disputed, so a queue is not somewhere to remember to look', async () => {
        stubApi(withFlags([{ id: 3 }, { id: 4 }]));

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="flags-link"]').text()).toContain('2');
    });

    it('offers the queue even when nothing is in it', async () => {
        stubApi(withFlags([]));

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="flags-link"]').text()).toContain('Disputed results');
    });
});
