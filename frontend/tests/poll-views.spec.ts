import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { createAppRouter } from '@/router';
import PollsView from '@/views/PollsView.vue';
import PollView from '@/views/PollView.vue';

const EVENT_SLUG = 'london-grand-tournament';

function eventBody(organise = false) {
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
                is_attendee: true,
                attendee_id: 9,
                permissions: { organise, register: false, manage_organisers: organise },
            },
        },
    };
}

function poll(overrides: Record<string, unknown> = {}) {
    return {
        id: 1,
        name: 'Best Painted Army',
        type: 'painting',
        votes_per_player: 2,
        opens_at: null,
        closes_at: null,
        is_open: false,
        is_open_for_me: false,
        my_ballot: [],
        ...overrides,
    };
}

const PULSE = { data: { current_round: null, rounds: null, standings: null, polls: 'a' } };
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

function mountView(component: unknown, props: Record<string, unknown> = { eventSlug: EVENT_SLUG }) {
    return mount(component as never, ({
        props,
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

    await router.push(`/events/${EVENT_SLUG}/polls`);
    await router.isReady();
});

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('the list of polls', () => {
    it('says which polls a Player may vote in now, and which are shut', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/polls`]: {
                status: 200,
                body: {
                    data: [
                        poll({ id: 1, name: 'Best Painted Army', is_open: true, is_open_for_me: true }),
                        poll({ id: 2, name: 'Favourite Opponent', type: 'favourite_opponent', is_open: true, is_open_for_me: false }),
                        poll({ id: 3, name: 'Best Sportsman', is_open: false, is_open_for_me: false, closes_at: '2026-09-13T17:00:00Z' }),
                    ],
                },
            },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(PollsView);
        await flushPromises();

        expect(view.get('[data-testid="poll-1"]').text()).toContain('Open');

        // Open, but not to this Player: their last game is still being played.
        expect(view.get('[data-testid="poll-2"]').text()).toContain('Not yet');

        expect(view.get('[data-testid="poll-3"]').text()).toContain('Closed');

        // Only the one they can act on is a way in.
        expect(view.get('[data-testid="poll-1"]').attributes('href')).toBe(`/events/${EVENT_SLUG}/polls/1`);
        expect(view.get('[data-testid="poll-3"]').attributes('href')).toBeUndefined();
    });
});

describe('casting a ballot', () => {
    const CANDIDATES = {
        data: [
            { id: 21, name: 'Sons of Terra', allegiance: 'loyalist', members: [{ id: 12, name: 'Ada Lovelace', faction: { id: 3, name: 'Imperial Fists' } }] },
            { id: 22, name: 'The Warmaster\'s Own', allegiance: 'traitor', members: [] },
            { id: 23, name: 'Third Company', allegiance: 'loyalist', members: [] },
        ],
    };

    function withPoll(overrides: Record<string, unknown> = {}) {
        return {
            [`/api/events/${EVENT_SLUG}/polls/1/candidates`]: { status: 200, body: CANDIDATES },
            [`/api/events/${EVENT_SLUG}/polls/1/ballot`]: { status: 200, body: { data: { poll_id: 1, attendee_ids: [] } } },
            [`/api/events/${EVENT_SLUG}/polls`]: {
                status: 200,
                body: { data: [poll({ id: 1, is_open: true, is_open_for_me: true, ...overrides })] },
            },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        };
    }

    beforeEach(async () => {
        await router.push(`/events/${EVENT_SLUG}/polls/1`);
    });

    it('sends the whole ballot, starting from what this Player last picked', async () => {
        const fetch = stubApi(withPoll({ my_ballot: [22] }));

        const view = mountView(PollView, { eventSlug: EVENT_SLUG, pollId: '1' });
        await flushPromises();

        // What they last sent, back on the screen: revising is the same act
        // as voting, so it cannot start from blank.
        expect(view.get('[data-testid="pick-22"]').classes().join(' ')).toContain('border-primary');

        await view.get('[data-testid="pick-21"]').trigger('click');
        await view.get('[data-testid="save-ballot"]').trigger('click');
        await flushPromises();

        const sent = fetch.mock.calls.find(([url]) => String(url).endsWith('/polls/1/ballot'))!;
        expect(sent[1]?.method).toBe('PUT');
        expect(JSON.parse(sent[1]?.body as string)).toEqual({ attendee_ids: [22, 21] });
    });

    it('holds the ballot to the poll\'s limit rather than letting the API refuse it', async () => {
        stubApi(withPoll({ votes_per_player: 2, my_ballot: [] }));

        const view = mountView(PollView, { eventSlug: EVENT_SLUG, pollId: '1' });
        await flushPromises();

        await view.get('[data-testid="pick-21"]').trigger('click');
        await view.get('[data-testid="pick-22"]').trigger('click');
        await view.get('[data-testid="pick-23"]').trigger('click');
        await flushPromises();

        expect(view.get('[data-testid="pick-23"]').classes().join(' ')).not.toContain('border-primary');
        expect(view.get('[data-testid="picks-left"]').text()).toContain('0');
    });

    it('says a poll that is not open to this Player is not open, and offers no picks', async () => {
        stubApi(withPoll({ is_open: false, is_open_for_me: false, closes_at: '2026-09-13T17:00:00Z' }));

        const view = mountView(PollView, { eventSlug: EVENT_SLUG, pollId: '1' });
        await flushPromises();

        expect(view.get('[data-testid="poll-shut"]').text()).toContain('closed');
        expect(view.find('[data-testid="save-ballot"]').exists()).toBe(false);
    });
});

describe('an organiser running the votes', () => {
    function withPolls(polls: unknown[]) {
        return {
            [`/api/events/${EVENT_SLUG}/polls/1/open`]: { status: 200, body: { data: poll({ is_open: true }) } },
            [`/api/events/${EVENT_SLUG}/polls/1/close`]: { status: 200, body: { data: poll({ is_open: false }) } },
            [`/api/events/${EVENT_SLUG}/polls/1/results`]: {
                status: 200,
                body: {
                    data: {
                        poll: { id: 1, name: 'Best Painted Army', type: 'painting', is_open: false },
                        tallies: [
                            { attendee: { id: 21, name: 'Sons of Terra', display_number: 4 }, votes: 11 },
                            { attendee: { id: 22, name: 'Third Company', display_number: 9 }, votes: 3 },
                        ],
                    },
                },
            },
            [`/api/events/${EVENT_SLUG}/polls`]: { status: 200, body: { data: polls } },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody(true) },
        };
    }

    it('opens a poll that has not opened, and closes one that is open', async () => {
        const fetch = stubApi(withPolls([poll({ id: 1, is_open: false })]));

        const view = mountView(PollsView);
        await flushPromises();

        await view.get('[data-testid="open-poll-1"]').trigger('click');
        await flushPromises();

        expect(fetch.mock.calls.some(([url]) => String(url).endsWith('/polls/1/open'))).toBe(true);

        stubApi(withPolls([poll({ id: 1, is_open: true })]));

        const open = mountView(PollsView);
        await flushPromises();

        expect(open.find('[data-testid="open-poll-1"]').exists()).toBe(false);
        expect(open.find('[data-testid="close-poll-1"]').exists()).toBe(true);
    });

    it('reads the tallies only once voting is over', async () => {
        stubApi(withPolls([poll({ id: 1, is_open: true })]));

        const open = mountView(PollsView);
        await flushPromises();

        // Live tallies would tell an organiser who is winning while people are
        // still voting, which is not a thing anyone should be able to say.
        expect(open.find('[data-testid="results-1"]').exists()).toBe(false);

        stubApi(withPolls([poll({ id: 1, is_open: false, closes_at: '2026-09-13T17:00:00Z' })]));

        const view = mountView(PollsView);
        await flushPromises();

        await view.get('[data-testid="show-results-1"]').trigger('click');
        await flushPromises();

        const results = view.get('[data-testid="results-1"]');
        expect(results.text()).toContain('Sons of Terra');
        expect(results.text()).toContain('11');
    });

    it('keeps tallies away from a Player entirely', async () => {
        stubApi(withPolls([poll({ id: 1, is_open: false, closes_at: '2026-09-13T17:00:00Z' })]));
        stubApi({
            [`/api/events/${EVENT_SLUG}/polls`]: { status: 200, body: { data: [poll({ id: 1, is_open: false, closes_at: '2026-09-13T17:00:00Z' })] } },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody(false) },
        });

        const view = mountView(PollsView);
        await flushPromises();

        expect(view.find('[data-testid="show-results-1"]').exists()).toBe(false);
        expect(view.find('[data-testid="open-poll-1"]').exists()).toBe(false);
    });
});
