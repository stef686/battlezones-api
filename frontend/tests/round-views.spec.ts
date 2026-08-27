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
        score_types: [
            { slug: 'match-points', name: 'Match Points' },
            { slug: 'victory-points', name: 'Victory Points' },
        ],
        games: [
            {
                id: 21,
                table_number: null,
                is_bye: true,
                result: { submitted_at: null, is_flagged: false },
                attendees: [{ id: 11, name: 'Odd One Out', is_winner: true, members: [], scores: {} }],
            },
            {
                id: 19,
                table_number: 5,
                is_bye: false,
                result: { submitted_at: null, is_flagged: false },
                attendees: [
                    { id: 9, name: 'Sons of Terra', is_winner: false, members: [], scores: {} },
                    { id: 10, name: 'The Warmaster\'s Own', is_winner: false, members: [], scores: {} },
                ],
            },
            {
                id: 18,
                table_number: 1,
                is_bye: false,
                result: { submitted_at: '2026-09-12T14:05:00Z', is_flagged: false },
                attendees: [
                    { id: 12, name: 'First Table', is_winner: true, members: [], scores: { 'match-points': '3.00', 'victory-points': '85.50' } },
                    { id: 13, name: 'Also First Table', is_winner: false, members: [], scores: { 'match-points': '0.00', 'victory-points': '70.00' } },
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

describe('the rounds tab', () => {
    it('takes a reader straight to the last published round', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: ROUNDS },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        mountView(RoundsView);
        await flushPromises();

        expect(router.currentRoute.value.name).toBe('round');
        expect(router.currentRoute.value.params.roundId).toBe('4');
    });

    it('leaves nothing to go back to, having replaced itself', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: ROUNDS },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const replace = vi.spyOn(router, 'replace');

        mountView(RoundsView);
        await flushPromises();

        expect(replace).toHaveBeenCalledTimes(1);
    });

    it('walks past a draft, which is not the round being played', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds`]: {
                status: 200,
                body: { data: [...ROUNDS.data, { id: 5, number: 3, name: null, status: 'draft' }] },
            },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        mountView(RoundsView);
        await flushPromises();

        expect(router.currentRoute.value.params.roundId).toBe('4');
    });

    it('lands an organiser on their draft when it is the only round there is', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds`]: {
                status: 200,
                body: { data: [{ id: 5, number: 1, name: null, status: 'draft' }] },
            },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        mountView(RoundsView);
        await flushPromises();

        expect(router.currentRoute.value.params.roundId).toBe('5');
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
        expect(router.currentRoute.value.name).toBe('rounds');
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
        expect(tables).toEqual(['Table 1', 'Table 5', 'Bye']);

        expect(view.get('[data-testid="pairing-19"]').text()).toContain('Sons of Terra');
        // The header already says Bye, so the card does not say it twice.
        expect(view.get('[data-testid="pairing-21"]').find('[data-testid="pairing-bye"]').exists()).toBe(false);
    });

    it('says nothing about a rematch to a reader the API did not tell', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds/4`]: { status: 200, body: ROUND },
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: ROUNDS },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RoundView, { eventSlug: EVENT_SLUG, roundId: '4' });
        await flushPromises();

        expect(view.find('[data-testid="pairing-rematch"]').exists()).toBe(false);
    });

    it('marks a rematch beside the table for the organiser who was told', async () => {
        const round = { data: { ...ROUND.data, games: ROUND.data.games.map((game) => ({ ...game, is_rematch: game.id === 19 })) } };

        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds/4`]: { status: 200, body: round },
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: ROUNDS },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RoundView, { eventSlug: EVENT_SLUG, roundId: '4' });
        await flushPromises();

        const badge = view.get('[data-testid="pairing-19"] [data-testid="pairing-rematch"]');

        // An icon, with the name it cannot carry itself kept for a screen reader.
        expect(badge.find('svg').exists()).toBe(true);
        expect(badge.get('.sr-only').text()).toBe('Rematch');

        // Beside the table number, not opposite it: same group, and the group
        // is the one the table leads.
        const leading = view.get('[data-testid="pairing-19"] header > span:first-child');

        expect(leading.find('[data-testid="pairing-table"]').exists()).toBe(true);
        expect(leading.find('[data-testid="pairing-rematch"]').exists()).toBe(true);

        expect(view.get('[data-testid="pairing-18"]').find('[data-testid="pairing-rematch"]').exists()).toBe(false);
    });

    it('names the score columns once, above the two teams', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds/4`]: { status: 200, body: ROUND },
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: ROUNDS },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RoundView, { eventSlug: EVENT_SLUG, roundId: '4' });
        await flushPromises();

        const headings = view.get('[data-testid="pairing-18"] [data-testid="pairing-columns"]');

        // The initials sit over the numbers; the full name goes to a screen
        // reader, so nothing rests on working out what MP means.
        expect(headings.get('[data-testid="column-match-points"]').text()).toContain('MP');
        expect(headings.get('[data-testid="column-victory-points"]').text()).toContain('Victory Points');

        // Once per card, not once per team.
        expect(view.get('[data-testid="pairing-18"]').findAll('[data-testid="pairing-columns"]')).toHaveLength(1);
    });

    it('marks the winning team, and leaves a game nobody won unmarked', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds/4`]: { status: 200, body: ROUND },
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: ROUNDS },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RoundView, { eventSlug: EVENT_SLUG, roundId: '4' });
        await flushPromises();

        const won = view.get('[data-testid="pairing-team-12"]');
        const lost = view.get('[data-testid="pairing-team-13"]');

        // Weighted and ticked, with the tick named for a screen reader.
        expect(won.get('th').classes()).toContain('font-semibold');
        expect(won.get('[data-testid="winner-12"]').find('svg').exists()).toBe(true);
        expect(won.get('[data-testid="winner-12"] .sr-only').text()).toBe('Won');

        expect(lost.get('th').classes()).toContain('font-normal');
        expect(lost.find('[data-testid="winner-13"]').exists()).toBe(false);

        // Still being played, so neither side is ahead of the other yet.
        expect(view.find('[data-testid="winner-9"]').exists()).toBe(false);
        expect(view.find('[data-testid="winner-10"]').exists()).toBe(false);
    });

    it('says a game is finished once its result is in', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds/4`]: { status: 200, body: ROUND },
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: ROUNDS },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RoundView, { eventSlug: EVENT_SLUG, roundId: '4' });
        await flushPromises();

        expect(view.get('[data-testid="pairing-18"]').find('[data-testid="pairing-finished"]').exists()).toBe(true);
        // Still being played, so it says what it is instead.
        expect(view.get('[data-testid="pairing-19"]').find('[data-testid="pairing-finished"]').exists()).toBe(false);
    });

    it('lines both teams up on the same score columns, in the same order', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds/4`]: { status: 200, body: ROUND },
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: ROUNDS },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RoundView, { eventSlug: EVENT_SLUG, roundId: '4' });
        await flushPromises();

        const played = view.get('[data-testid="pairing-18"]');

        expect(played.get('[data-testid="pairing-team-12"]').text()).toContain('First Table');
        // The column holds two decimal places so half points survive; a card
        // read at a glance shows 3, not 3.00 — and 85.5 rather than 85.50.
        expect(played.get('[data-testid="pairing-team-12"]').findAll('[data-testid^="score-"]').map((n) => n.text()))
            .toEqual(['3', '85.5']);
        expect(played.get('[data-testid="pairing-team-13"]').findAll('[data-testid^="score-"]').map((n) => n.text()))
            .toEqual(['0', '70']);

        // A Game nobody has played yet shows the columns it is waiting on,
        // scored at zero — not a blank where the numbers will go.
        expect(view.get('[data-testid="pairing-team-9"]').findAll('[data-testid^="score-"]').map((n) => n.text()))
            .toEqual(['0', '0']);
    });

    it('moves between rounds on the chevrons either side of the name', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds/4`]: { status: 200, body: ROUND },
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: ROUNDS },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RoundView, { eventSlug: EVENT_SLUG, roundId: '4' });
        await flushPromises();

        expect(view.get('[data-testid="round-name"]').text()).toBe('Round 2');
        expect(view.get('[data-testid="previous-round"]').attributes('href'))
            .toBe(`/events/${EVENT_SLUG}/rounds/3`);
        expect(view.get('[data-testid="previous-round"]').classes()).toContain('text-primary');
    });

    it('greys the chevron at the end of the rounds rather than dropping it', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds/4`]: { status: 200, body: ROUND },
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: ROUNDS },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RoundView, { eventSlug: EVENT_SLUG, roundId: '4' });
        await flushPromises();

        // Round 2 is the last one there is, so there is nowhere forward to go —
        // but the arrow stays put, or the name shifts sideways at the ends.
        const forward = view.get('[data-testid="next-round"]');

        expect(forward.attributes('href')).toBeUndefined();
        expect(forward.classes()).toContain('text-muted-foreground');
        expect(forward.attributes('aria-hidden')).toBe('true');
    });

    it('names the round a chevron leads to, so it is not just an arrow', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds/4`]: { status: 200, body: ROUND },
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: ROUNDS },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RoundView, { eventSlug: EVENT_SLUG, roundId: '4' });
        await flushPromises();

        expect(view.get('[data-testid="previous-round"]').attributes('aria-label')).toBe('Go to Round 1');
    });

    it('filters the games down to a team the reader is looking for', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds/4`]: { status: 200, body: ROUND },
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: ROUNDS },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RoundView, { eventSlug: EVENT_SLUG, roundId: '4' });
        await flushPromises();

        await view.get('[data-testid="game-search"]').setValue('sons of');

        // Matched on either team of the game, and case does not matter.
        expect(view.findAll('[data-testid^="pairing-1"]').map((row) => row.attributes('data-testid')))
            .toEqual(['pairing-19']);
        expect(view.find('[data-testid="pairing-18"]').exists()).toBe(false);
        expect(view.find('[data-testid="pairing-21"]').exists()).toBe(false);
    });

    it('spends no line on a label, and searches from the placeholder alone', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds/4`]: { status: 200, body: ROUND },
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: ROUNDS },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RoundView, { eventSlug: EVENT_SLUG, roundId: '4' });
        await flushPromises();

        const field = view.get('[data-testid="game-search"]');

        expect(field.attributes('type')).toBe('search');
        expect(field.attributes('placeholder')).toContain('team name');

        // Off the screen, still in the markup: an input with no accessible
        // name says nothing to a screen reader.
        const label = view.get(`label[for="${field.attributes('id')}"]`);

        expect(label.classes()).toContain('sr-only');
        expect(label.text()).toBe('Search by team name');
    });

    it('says nothing matched rather than reading as a round with no games', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/rounds/4`]: { status: 200, body: ROUND },
            [`/api/events/${EVENT_SLUG}/rounds`]: { status: 200, body: ROUNDS },
            [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RoundView, { eventSlug: EVENT_SLUG, roundId: '4' });
        await flushPromises();

        await view.get('[data-testid="game-search"]').setValue('nobody here');

        expect(view.get('[data-testid="pairings-unmatched"]').text()).toContain('nobody here');
        expect(view.find('[data-testid="pairings-empty"]').exists()).toBe(false);
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
