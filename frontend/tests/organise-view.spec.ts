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
    it('is not there at all for a reader without the permission', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody(false) },
        });

        const view = mountView();
        await flushPromises();

        // The same answer as an Event that does not exist: whether someone
        // else's Event has an organiser screen is not this reader's business.
        expect(view.find('[data-testid="missing"]').exists()).toBe(true);
        expect(view.find('[data-testid="generate-round"]').exists()).toBe(false);
        expect(view.find('[data-testid="publish-round"]').exists()).toBe(false);
    });

    it('offers the controls to an organiser', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: { data: [] } },
            [`/api/events/${EVENT_SLUG}/standings`]: { status: 200, body: { data: [] } },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: { data: { current_round: null, rounds: null, standings: null, polls: null } } },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView();
        await flushPromises();

        expect(view.find('[data-testid="missing"]').exists()).toBe(false);
        expect(view.find('[data-testid="generate-round"]').exists()).toBe(true);
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

    it('names the tables still playing', async () => {
        stubApi(withLiveRound([
            pairing({ id: 18, table_number: 1, result: { submitted_at: '2026-09-12T12:00:00Z', is_flagged: false } }),
            pairing({ id: 19, table_number: 5 }),
            pairing({ id: 20, table_number: 6 }),
        ]));

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="outstanding-count"]').text()).toBe('2 tables to go');
        expect(view.get('[data-testid="outstanding-19"]').text()).toBe('5');
        expect(view.find('[data-testid="outstanding-18"]').exists()).toBe(false);
    });

    it('never counts a bye, since nobody can report one', async () => {
        stubApi(withLiveRound([
            pairing({ id: 18, table_number: 1, result: { submitted_at: '2026-09-12T12:00:00Z', is_flagged: false } }),
            pairing({ id: 21, table_number: null, is_bye: true, attendees: [] }),
        ]));

        const view = mountView();
        await flushPromises();

        expect(view.find('[data-testid="all-reported"]').exists()).toBe(true);
    });

    it('says so when every table has reported', async () => {
        stubApi(withLiveRound([
            pairing({ result: { submitted_at: '2026-09-12T12:00:00Z', is_flagged: false } }),
        ]));

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="all-reported"]').text()).toContain('reported');
    });
});

describe('reviewing a draft round', () => {
    function withDraft(games: unknown[]) {
        return {
            [`/api/events/${EVENT_SLUG}/rounds/4`]: {
                status: 200,
                body: { data: { id: 4, number: 2, name: null, status: 'draft', games } },
            },
            [`/api/events/${EVENT_SLUG}/rounds`]: {
                status: 200,
                body: { data: [{ id: 4, number: 2, name: null, status: 'draft' }] },
            },
            [`/api/events/${EVENT_SLUG}/standings`]: { status: 200, body: STANDINGS },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: { data: { current_round: null, rounds: 'a', standings: null, polls: null } } },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        };
    }

    it('shows each pairing with both allegiances and where the teams stand', async () => {
        stubApi(withDraft([pairing()]));

        const view = mountView();
        await flushPromises();

        const review = view.get('[data-testid="review-18"]');

        expect(review.get('[data-testid="review-table"]').text()).toBe('1');
        expect(review.find('[data-testid="allegiance-loyalist"]').exists()).toBe(true);
        expect(review.find('[data-testid="allegiance-traitor"]').exists()).toBe(true);

        expect(review.findAll('[data-testid="review-position"]').map((node) => node.text()))
            .toEqual(['#1', '#4']);
    });

    it('warns when a game is not between opposed allegiances', async () => {
        stubApi(withDraft([pairing({
            attendees: [
                { id: 9, name: 'Sons of Terra', allegiance: 'loyalist', members: [], scores: {} },
                { id: 10, name: 'Also Loyal', allegiance: 'loyalist', members: [], scores: {} },
            ],
        })]));

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="unopposed-warning"]').text()).toContain('not between opposed');
    });

    it('does not warn about a bye, which has nobody to oppose', async () => {
        stubApi(withDraft([pairing({ is_bye: true, table_number: null, attendees: [
            { id: 9, name: 'Sons of Terra', allegiance: 'loyalist', members: [], scores: {} },
        ] })]));

        const view = mountView();
        await flushPromises();

        expect(view.find('[data-testid="unopposed-warning"]').exists()).toBe(false);
        expect(view.find('[data-testid="review-bye"]').exists()).toBe(true);
    });

    it('offers publish rather than pair once a draft exists', async () => {
        stubApi(withDraft([pairing()]));

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="publish-round"]').text()).toContain('Round 2');
        expect(view.find('[data-testid="generate-round"]').exists()).toBe(false);
        // Withdrawing is about a published Round, so it is not offered here.
        expect(view.find('[data-testid="unpublish-round"]').exists()).toBe(false);
    });

    it('publishes the draft', async () => {
        const fetch = stubApi({
            ...withDraft([pairing()]),
            [`/api/events/${EVENT_SLUG}/rounds/4/publish`]: {
                status: 200,
                body: { data: { id: 4, number: 2, name: null, status: 'live', games: [] } },
            },
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="publish-round"]').trigger('click');
        await flushPromises();

        const published = fetch.mock.calls.find(([url]) => String(url).endsWith('/rounds/4/publish'))!;
        expect(published[1]?.method).toBe('POST');
    });
});

describe('pairing the next round', () => {
    const EMPTY = {
        [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: { data: [{ id: 3, number: 1, name: 'Round 1', status: 'live' }] } },
        [`/api/events/${EVENT_SLUG}/rounds/3`]: {
            status: 200,
            body: { data: { id: 3, number: 1, name: 'Round 1', status: 'live', games: [pairing({ result: { submitted_at: '2026-09-12T12:00:00Z', is_flagged: false } })] } },
        },
        [`/api/events/${EVENT_SLUG}/standings`]: { status: 200, body: STANDINGS },
        [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: { data: { current_round: { id: 3, number: 1 }, rounds: 'a', standings: null, polls: null } } },
        [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
    };

    it('asks the API to pair the field', async () => {
        const fetch = stubApi({
            ...EMPTY,
            [`/api/events/${EVENT_SLUG}/rounds`]: EMPTY[`/api/events/${EVENT_SLUG}/rounds`],
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="generate-round"]').trigger('click');
        await flushPromises();

        const generated = fetch.mock.calls.find(([url, init]) => String(url).endsWith('/rounds') && init?.method === 'POST');
        expect(generated).toBeTruthy();
    });

    it('repeats the API\'s reason when the field cannot be paired yet', async () => {
        stubApi(EMPTY);

        const view = mountView();
        await flushPromises();

        stubApi({
            ...EMPTY,
            [`/api/events/${EVENT_SLUG}/rounds`]: {
                status: 422,
                body: { message: 'Round 1 still has results outstanding, so the next Round cannot be paired.' },
            },
        });

        await view.get('[data-testid="generate-round"]').trigger('click');
        await flushPromises();

        // The message names what to put right, so it is shown as it stands
        // rather than reduced to "that could not be done".
        expect(view.get('[data-testid="organise-problem"]').text()).toContain('results outstanding');
    });

    it('withdraws a published round, and repeats the refusal when it is too late', async () => {
        const fetch = stubApi(EMPTY);

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="unpublish-round"]').text()).toContain('Round 1');

        await view.get('[data-testid="unpublish-round"]').trigger('click');
        await flushPromises();

        const withdrawn = fetch.mock.calls.find(([url, init]) => String(url).endsWith('/rounds/3/publish') && init?.method === 'DELETE');
        expect(withdrawn).toBeTruthy();

        stubApi({
            ...EMPTY,
            [`/api/events/${EVENT_SLUG}/rounds/3/publish`]: {
                status: 422,
                body: { message: 'Round 1 already has results, so it cannot be unpublished.' },
            },
        });

        await view.get('[data-testid="unpublish-round"]').trigger('click');
        await flushPromises();

        expect(view.get('[data-testid="organise-problem"]').text()).toContain('already has results');
    });
});

describe('swapping two pairings', () => {
    function twoTables() {
        const games = [
            pairing({
                id: 18,
                table_number: 1,
                attendees: [
                    { id: 9, name: 'Loyal One', allegiance: 'loyalist', members: [], scores: {} },
                    { id: 10, name: 'Traitor One', allegiance: 'traitor', members: [], scores: {} },
                ],
            }),
            pairing({
                id: 19,
                table_number: 2,
                attendees: [
                    { id: 11, name: 'Loyal Two', allegiance: 'loyalist', members: [], scores: {} },
                    { id: 12, name: 'Traitor Two', allegiance: 'traitor', members: [], scores: {} },
                ],
            }),
        ];

        return {
            [`/api/events/${EVENT_SLUG}/rounds/4`]: {
                status: 200,
                body: { data: { id: 4, number: 2, name: null, status: 'draft', games } },
            },
            [`/api/events/${EVENT_SLUG}/rounds`]: {
                status: 200,
                body: { data: [{ id: 4, number: 2, name: null, status: 'draft' }] },
            },
            [`/api/events/${EVENT_SLUG}/standings`]: { status: 200, body: STANDINGS },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: { data: { current_round: null, rounds: 'a', standings: null, polls: null } } },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        };
    }

    it('shows what the swap would produce before it is committed', async () => {
        stubApi(twoTables());

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="swap-18"]').trigger('click');
        expect(view.get('[data-testid="swap-prompt"]').text()).toContain('choose the game');

        await view.get('[data-testid="swap-19"]').trigger('click');
        await flushPromises();

        const preview = view.get('[data-testid="swap-preview"]');

        // Each table keeps the team already sitting at it; only the opponent
        // changes, and both games stay opposed.
        expect(preview.get('[data-testid="preview-18"]').text()).toContain('Loyal One');
        expect(preview.get('[data-testid="preview-18"]').text()).toContain('Traitor Two');
        expect(view.find('[data-testid="swap-unopposed"]').exists()).toBe(false);
    });

    it('sends both games and nothing else, because the exchange is not a choice', async () => {
        const fetch = stubApi({
            ...twoTables(),
            [`/api/events/${EVENT_SLUG}/rounds/4/swap`]: {
                status: 200,
                body: { data: { id: 4, number: 2, name: null, status: 'draft', games: [] } },
            },
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="swap-18"]').trigger('click');
        await view.get('[data-testid="swap-19"]').trigger('click');
        await view.get('[data-testid="confirm-swap"]').trigger('click');
        await flushPromises();

        const swap = fetch.mock.calls.find(([url]) => String(url).endsWith('/rounds/4/swap'))!;
        expect(JSON.parse(swap[1]?.body as string)).toEqual({ game_ids: [18, 19] });
    });

    it('repeats the API\'s refusal, which knows things one round cannot show', async () => {
        stubApi(twoTables());

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="swap-18"]').trigger('click');
        await view.get('[data-testid="swap-19"]').trigger('click');

        stubApi({
            ...twoTables(),
            [`/api/events/${EVENT_SLUG}/rounds/4/swap`]: {
                status: 422,
                body: { message: 'A Bye has to stay with the Allegiance that has more Attendees, or the Round cannot be paired.' },
            },
        });

        await view.get('[data-testid="confirm-swap"]').trigger('click');
        await flushPromises();

        expect(view.get('[data-testid="organise-problem"]').text()).toContain('more Attendees');
    });

    it('lets the organiser back out without swapping anything', async () => {
        stubApi(twoTables());

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="swap-18"]').trigger('click');
        await view.get('[data-testid="swap-19"]').trigger('click');
        await view.get('[data-testid="cancel-swap"]').trigger('click');
        await flushPromises();

        expect(view.find('[data-testid="swap-preview"]').exists()).toBe(false);
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
