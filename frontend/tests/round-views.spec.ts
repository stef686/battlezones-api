import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { defineComponent, h } from 'vue';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { keys } from '@/api/keys';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { useEventPulse } from '@/composables/useEventPulse';
import { createAppRouter } from '@/router';
import MyGameView from '@/views/MyGameView.vue';
import RoundsView from '@/views/RoundsView.vue';
import RoundView from '@/views/RoundView.vue';

const EVENT_SLUG = 'london-grand-tournament';

function eventBody(status = 'active') {
    return {
        data: {
            id: 1,
            name: 'London Grand Tournament',
            slug: EVENT_SLUG,
            description: null,
            status,
            starts_at: '2026-09-12T09:00:00Z',
            ends_at: '2026-09-13T18:00:00Z',
            max_attendees: null,
            attendee_size: 2,
            requires_allegiance: true,
            registration_closes_at: null,
            is_full: false,
            game_system: null,
            venue: { name: null, address: null, city: null, country: null },
            documents: [],
            viewer: null,
        },
    };
}

const ROUNDS = {
    data: [
        { id: 3, number: 1, name: 'Round 1', status: 'live' },
        { id: 4, number: 2, name: null, status: 'live' },
    ],
};

const ROUND = {
    data: {
        id: 4,
        number: 2,
        name: null,
        status: 'live',
        games: [
            {
                id: 21,
                table_number: null,
                is_bye: true,
                is_rematch: false,
                attendees: [{ id: 11, name: 'Odd One Out', members: [], scores: {} }],
            },
            {
                id: 19,
                table_number: 5,
                is_bye: false,
                is_rematch: true,
                attendees: [
                    { id: 9, name: 'Sons of Terra', members: [], scores: {} },
                    { id: 10, name: 'The Warmaster\'s Own', members: [], scores: {} },
                ],
            },
            {
                id: 18,
                table_number: 1,
                is_bye: false,
                is_rematch: false,
                attendees: [
                    { id: 12, name: 'First Table', members: [], scores: {} },
                    { id: 13, name: 'Also First Table', members: [], scores: {} },
                ],
            },
        ],
    },
};

const PULSE = {
    data: {
        current_round: { id: 4, number: 2 },
        rounds: '2026-09-12T13:30:00Z',
        standings: null,
        polls: null,
    },
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

function plugins(): never[] {
    return [pinia, router, [VueQueryPlugin, { queryClient }]] as never[];
}

function mountView(component: unknown, props: Record<string, unknown> = { eventSlug: EVENT_SLUG }) {
    return mount(component as never, ({ props, global: { plugins: plugins() } }) as never);
}

beforeEach(async () => {
    window.localStorage.clear();
    pinia = createPinia();
    setActivePinia(pinia);

    queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });

    router = createAppRouter();
    createApiClient(router, { baseUrl: 'https://api.test', storage: new InMemoryTokenStorage() });

    await router.push(`/events/${EVENT_SLUG}/rounds`);
    await router.isReady();
});

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('the rounds list', () => {
    it('lists the rounds it was sent, marking the one being played', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: ROUNDS },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RoundsView);
        await flushPromises();

        expect(view.get('[data-testid="round-3"]').text()).toContain('Round 1');
        // Unnamed rounds fall back to their number rather than showing blank.
        expect(view.get('[data-testid="round-4"]').text()).toContain('Round 2');

        expect(view.get('[data-testid="round-4"]').find('[data-testid="now-playing"]').exists()).toBe(true);
        expect(view.get('[data-testid="round-3"]').find('[data-testid="now-playing"]').exists()).toBe(false);
    });

    it('shows a Player nothing about drafts, because the API sends them none', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: ROUNDS },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RoundsView);
        await flushPromises();

        expect(view.find('[data-testid="draft-badge"]').exists()).toBe(false);
    });

    it('marks a draft for the organiser who was sent one', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds`]: {
                status: 200,
                body: { data: [...ROUNDS.data, { id: 5, number: 3, name: null, status: 'draft' }] },
            },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RoundsView);
        await flushPromises();

        expect(view.get('[data-testid="round-5"]').find('[data-testid="draft-badge"]').exists()).toBe(true);
    });

    it('says pairings will appear rather than showing an empty page', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: { data: [] } },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RoundsView);
        await flushPromises();

        expect(view.get('[data-testid="rounds-empty"]').text()).toContain('published');
    });
});

describe('the round detail', () => {
    beforeEach(async () => {
        await router.push(`/events/${EVENT_SLUG}/rounds/4`);
    });

    it('carries no back link, because the rounds chip is pinned a tap away', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds/4`]: { status: 200, body: ROUND },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RoundView, { eventSlug: EVENT_SLUG, roundId: '4' });
        await flushPromises();

        expect(view.find('[data-testid="back-to-rounds"]').exists()).toBe(false);
    });

    it('shows every pairing with its table number, tables first and in order', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds/4`]: { status: 200, body: ROUND },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RoundView, { eventSlug: EVENT_SLUG, roundId: '4' });
        await flushPromises();

        const tables = view.findAll('[data-testid="pairing-table"]').map((node) => node.text());
        // Table 1, then table 5, then the Bye — which has no table to cross to.
        expect(tables).toEqual(['1', '5', '—']);

        expect(view.get('[data-testid="pairing-19"]').text()).toContain('Sons of Terra');
        expect(view.get('[data-testid="pairing-19"]').find('[data-testid="pairing-rematch"]').exists()).toBe(true);
        expect(view.get('[data-testid="pairing-21"]').find('[data-testid="pairing-bye"]').exists()).toBe(true);
    });

    it('answers a draft round the same way as one that does not exist', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RoundView, { eventSlug: EVENT_SLUG, roundId: '9' });
        await flushPromises();

        const notice = view.get('[data-testid="missing"]').text().toLowerCase();
        expect(notice).toContain('not found');
        expect(notice).not.toContain('draft');
        expect(notice).not.toContain('permission');
    });
});

describe('the pulse', () => {
    /** A bare host, so the composable is exercised without a screen around it. */
    function hostFor(inProgress: boolean) {
        return defineComponent({
            setup() {
                const pulse = useEventPulse(EVENT_SLUG, inProgress);

                return () => h('span', { 'data-testid': 'polling' }, String(pulse.isPolling.value));
            },
        });
    }

    it('retires the rounds when a round is published, and nothing else', async () => {
        stubApi({ [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE } });

        // Both resources are already cached and fresh.
        queryClient.setQueryData(keys.rounds(EVENT_SLUG), ROUNDS.data);
        queryClient.setQueryData(keys.standings(EVENT_SLUG), []);

        mount(hostFor(true), { global: { plugins: plugins() } } as never);
        await flushPromises();

        stubApi({
            [`/api/events/${EVENT_SLUG}/pulse`]: {
                status: 200,
                body: { data: { ...PULSE.data, current_round: { id: 5, number: 3 }, rounds: '2026-09-12T15:00:00Z' } },
            },
        });

        await queryClient.refetchQueries({ queryKey: keys.pulse(EVENT_SLUG) });
        await flushPromises();

        expect(queryClient.getQueryState(keys.rounds(EVENT_SLUG))?.isInvalidated).toBe(true);
        // A published Round says nothing about the Standings.
        expect(queryClient.getQueryState(keys.standings(EVENT_SLUG))?.isInvalidated).toBe(false);
    });

    it('does not poll an event that is not being run', async () => {
        const fetch = stubApi({ [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE } });

        const view = mount(hostFor(false), { global: { plugins: plugins() } } as never);
        await flushPromises();

        expect(view.get('[data-testid="polling"]').text()).toBe('false');
        expect(fetch).not.toHaveBeenCalled();
    });

    it('polls an event that is being run', async () => {
        const fetch = stubApi({ [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE } });

        const view = mount(hostFor(true), { global: { plugins: plugins() } } as never);
        await flushPromises();

        expect(view.get('[data-testid="polling"]').text()).toBe('true');
        expect(fetch).toHaveBeenCalledTimes(1);
    });
});

describe('a Player with the bye', () => {
    it('is told it counts as a win, and is offered nothing to submit', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/my-game`]: {
                status: 200,
                body: {
                    data: {
                        id: 21,
                        table_number: null,
                        is_bye: true,
                        round: { id: 4, number: 2, name: 'Round 2' },
                        result: { submitted_at: null, edited_at: null, is_flagged: false },
                        attendees: [{ id: 9, name: 'Odd One Out', members: [], scores: {} }],
                    },
                },
            },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mount(MyGameView as never, ({
            props: { eventSlug: EVENT_SLUG },
            global: { plugins: plugins() },
        }) as never);
        await flushPromises();

        expect(view.get('[data-testid="bye-notice"]').text()).toContain('win');

        // The API refuses a result for a Bye, so offering the form would be
        // inviting the Player to fail.
        expect(view.find('[data-testid="submit-result"]').exists()).toBe(false);
        expect(view.find('[data-testid="my-score"]').exists()).toBe(false);
        expect(view.find('[data-testid="opponent"]').exists()).toBe(false);

        // No table to cross the hall to.
        expect(view.get('[data-testid="table-number"]').text()).toBe('—');
    });
});

describe('a Player looking up what they will face', () => {
    it('links from their game to the opposing team, where revealed lists live', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/my-game`]: {
                status: 200,
                body: {
                    data: {
                        id: 18,
                        table_number: 5,
                        is_bye: false,
                        round: { id: 4, number: 2, name: 'Round 2' },
                        result: { submitted_at: null, edited_at: null, is_flagged: false },
                        attendees: [
                            { id: 9, name: 'Mine', members: [], scores: {} },
                            { id: 10, name: 'Theirs', members: [], scores: {} },
                        ],
                    },
                },
            },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(MyGameView);
        await flushPromises();

        expect(view.get('[data-testid="opponent-link"]').attributes('href'))
            .toBe(`/events/${EVENT_SLUG}/attendees/10`);
    });
});

describe('a Player who disagrees with the result', () => {
    function submittedGame(overrides: Record<string, unknown> = {}) {
        return {
            data: {
                id: 18,
                table_number: 5,
                is_bye: false,
                round: { id: 4, number: 2, name: 'Round 2' },
                result: { submitted_at: '2026-09-12T14:05:00Z', edited_at: null, is_flagged: false, ...overrides },
                attendees: [
                    { id: 9, name: 'Mine', members: [], scores: { 'victory-points': 70 } },
                    { id: 10, name: 'Theirs', members: [], scores: { 'victory-points': 85 } },
                ],
            },
        };
    }

    it('flags it for an organiser, in their own words', async () => {
        const fetch = stubApi({
            [`/api/events/${EVENT_SLUG}/my-game`]: { status: 200, body: submittedGame() },
            [`/api/events/${EVENT_SLUG}/games/18/flag`]: { status: 200, body: { data: {} } },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(MyGameView);
        await flushPromises();

        await view.get('[data-testid="flag-reason"]').setValue('We agreed 85-70 the other way round.');
        await view.get('[data-testid="flag-result"]').trigger('click');
        await flushPromises();

        const flagged = fetch.mock.calls.find(([url]) => String(url).endsWith('/games/18/flag'))!;
        expect(flagged[1]?.method).toBe('POST');
        expect(JSON.parse(flagged[1]?.body as string)).toEqual({ reason: 'We agreed 85-70 the other way round.' });
    });

    it('is told an organiser has it, and is not asked to flag it twice', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/my-game`]: { status: 200, body: submittedGame({ is_flagged: true }) },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(MyGameView);
        await flushPromises();

        expect(view.get('[data-testid="result-flagged"]').text()).toContain('organiser');
        expect(view.find('[data-testid="flag-form"]').exists()).toBe(false);
    });

    it('sees who corrected the result, since the change was not theirs', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/my-game`]: {
                status: 200,
                body: submittedGame({
                    edited_at: '2026-09-12T15:00:00Z',
                    edited_by: { id: 4, name: 'Rogal Dorn' },
                }),
            },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(MyGameView);
        await flushPromises();

        expect(view.get('[data-testid="result-corrected"]').text()).toContain('Rogal Dorn');
    });
});
