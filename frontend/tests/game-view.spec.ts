import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { createAppRouter } from '@/router';
import GameView from '@/views/GameView.vue';
import RoundView from '@/views/RoundView.vue';

const EVENT_SLUG = 'london-grand-tournament';

const EVENT = {
    data: {
        id: 1,
        name: 'London Grand Tournament',
        slug: EVENT_SLUG,
        description: null,
        status: 'active',
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

const PULSE = {
    data: { current_round: { id: 4, number: 2 }, rounds: null, standings: null, polls: null },
};

const ROUND = {
    data: {
        id: 4,
        number: 2,
        name: null,
        status: 'live',
        score_types: [
            { slug: 'match-points', name: 'Match Points', is_primary: false },
            { slug: 'victory-points', name: 'Victory Points', is_primary: true },
        ],
        games: [
            {
                id: 18,
                table_number: 1,
                is_bye: false,
                result: { submitted_at: null, is_flagged: false },
                attendees: [
                    { id: 9, name: 'Sons of Terra', is_winner: false, members: [], scores: {} },
                    { id: 10, name: 'The Warmaster\'s Own', is_winner: false, members: [], scores: {} },
                ],
            },
        ],
    },
};

/** A doubles Game: two teams, two Players each, one team's lists open. */
const GAME = {
    data: {
        id: 18,
        table_number: 1,
        is_bye: false,
        round: { id: 4, number: 2, name: null },
        score_types: [
            { slug: 'match-points', name: 'Match Points', is_primary: false },
            { slug: 'victory-points', name: 'Victory Points', is_primary: true },
        ],
        result: {
            submitted_at: '2026-09-12T14:05:00Z',
            submitted_by: { id: 12, name: 'Ada Lovelace' },
            edited_at: null,
            edited_by: null,
            is_flagged: false,
        },
        attendees: [
            {
                id: 9,
                name: 'Sons of Terra',
                is_winner: true,
                scores: { 'match-points': '3.00', 'victory-points': '85.50' },
                members: [
                    {
                        id: 12,
                        name: 'Ada Lovelace',
                        faction: { id: 3, name: 'Sons of Horus' },
                        army_list_locked: true,
                        army_list: 'Legion Tactical Squad, 10 models',
                    },
                    {
                        id: 13,
                        name: 'Grace Hopper',
                        faction: null,
                        army_list_locked: true,
                        army_list: 'Contemptor Dreadnought',
                    },
                ],
            },
            {
                id: 10,
                name: 'The Warmaster\'s Own',
                is_winner: false,
                scores: { 'match-points': '0.00', 'victory-points': '70.00' },
                members: [
                    { id: 14, name: 'Alan Turing', faction: null, army_list_locked: true },
                    { id: 15, name: 'Katherine Johnson', faction: null, army_list_locked: false },
                ],
            },
        ],
    },
};

const NOT_FOUND = { status: 404, body: { message: 'Not Found.' } };

function stubApi(routes: Record<string, { status: number; body?: unknown }>) {
    const fetch = vi.fn((url: string) => {
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

function stubEverything(overrides: Record<string, { status: number; body?: unknown }> = {}) {
    return stubApi({
        [`/api/events/${EVENT_SLUG}/games/18`]: { status: 200, body: GAME },
        [`/api/events/${EVENT_SLUG}/rounds/4`]: { status: 200, body: ROUND },
        [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
        [`/api/events/${EVENT_SLUG}`]: { status: 200, body: EVENT },
        ...overrides,
    });
}

let router: Router;
let pinia: ReturnType<typeof createPinia>;
let queryClient: QueryClient;

function plugins(): never[] {
    return [pinia, router, [VueQueryPlugin, { queryClient }]] as never[];
}

function mountView(component: unknown, props: Record<string, unknown>) {
    return mount(component as never, ({ props, global: { plugins: plugins() } }) as never);
}

async function mountGame() {
    await router.push(`/events/${EVENT_SLUG}/games/18`);

    const view = mountView(GameView, { eventSlug: EVENT_SLUG, gameId: '18' });
    await flushPromises();

    return view;
}

beforeEach(async () => {
    window.localStorage.clear();
    pinia = createPinia();
    setActivePinia(pinia);

    queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });

    router = createAppRouter();
    createApiClient(router, { baseUrl: 'https://api.test', storage: new InMemoryTokenStorage() });

    await router.push(`/events/${EVENT_SLUG}/rounds/4`);
    await router.isReady();
});

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('opening a game from the round', () => {
    it('makes the whole card the way in, rather than a word inside it', async () => {
        stubEverything();

        const view = mountView(RoundView, { eventSlug: EVENT_SLUG, roundId: '4' });
        await flushPromises();

        const card = view.get('[data-testid="open-game-18"]');

        expect(card.attributes('href')).toBe(`/events/${EVENT_SLUG}/games/18`);
        // The scoreline is inside the target, so a Player aims at the card.
        expect(card.text()).toContain('Sons of Terra');
    });
});

describe('the game detail', () => {
    it('leads with the scoreline the card it was tapped from carried', async () => {
        stubEverything();

        const view = await mountGame();

        expect(view.get('[data-testid="game-name"]').text()).toBe('Table 1');
        expect(view.get('[data-testid="column-match-points"]').text()).toContain('MP');

        // One game on screen, so the columns are named where they are read
        // rather than hidden the way the round's run of cards hides them.
        expect(view.get('[data-testid="pairing-columns"]').classes()).not.toContain('sr-only');
        expect(view.get('[data-testid="pairing-team-9"]').text()).toContain('Sons of Terra');
        expect(view.get('[data-testid="pairing-team-9"]').text()).toContain('85.5');
        expect(view.find('[data-testid="winner-9"]').exists()).toBe(true);
        expect(view.find('[data-testid="winner-10"]').exists()).toBe(false);
        expect(view.get('[data-testid="game-finished"]').text()).toBe('Finished');

        // The same pill the round's rows wear, on both facts.
        expect(view.get('[data-testid="game-name"]').classes()).toContain('game-label');
        expect(view.get('[data-testid="game-finished"]').classes()).toContain('game-label');

        // A rule under the scoreline, matching the one between games on the
        // round, so the numbers end somewhere rather than running into the
        // lists below them.
        expect(view.get('[data-testid="game-scoreline"]').classes())
            .toEqual(expect.arrayContaining(['border-b', 'border-card-divider']));
    });

    it('goes back to the round, which the rounds tab reaches and this is under', async () => {
        stubEverything();

        const view = await mountGame();

        const back = view.get('[data-testid="back-to-round"]');

        expect(back.attributes('href')).toBe(`/events/${EVENT_SLUG}/rounds/4`);
        expect(back.text()).toContain('Round 2');
    });

    it('gives each side of the table a tab, named for the team, and opens the first', async () => {
        stubEverything();

        const view = await mountGame();

        const tabs = view.findAll('[role="tab"]');

        expect(tabs.map((tab) => tab.text())).toEqual(['Sons of Terra', 'The Warmaster\'s Own']);
        expect(tabs[0]?.attributes('aria-selected')).toBe('true');
        // A tablist is one tab stop: the arrows move between the rest.
        expect(tabs.map((tab) => tab.attributes('tabindex'))).toEqual(['0', '-1']);

        // An even half each, with a name too long for its half cut rather
        // than allowed to squeeze its opponent's out of the way.
        tabs.forEach((tab) => {
            expect(tab.classes()).toContain('flex-1');
            expect(tab.classes()).toContain('truncate');
            expect(tab.attributes('title')).toBe(tab.text());
        });
    });

    it('reads both of a team\'s lists together, rather than one player at a time', async () => {
        stubEverything();

        const view = await mountGame();

        const panel = view.get('[data-testid="army-list-panel-9"]');

        expect(panel.get('[data-testid="member-12"]').text()).toContain('Ada Lovelace');
        expect(panel.get('[data-testid="army-list-12"]').text()).toBe('Legion Tactical Squad, 10 models');
        expect(panel.get('[data-testid="member-13"]').text()).toContain('Grace Hopper');
        expect(panel.get('[data-testid="army-list-13"]').text()).toBe('Contemptor Dreadnought');

        expect(view.get('[data-testid="member-13"] [data-testid="member-faction"]').text()).toBe('Faction not chosen');
    });

    it('shows the lists of whichever team is tapped', async () => {
        stubEverything();

        const view = await mountGame();

        await view.get('[data-testid="army-list-tab-10"]').trigger('click');

        expect(view.find('[data-testid="army-list-panel-9"]').exists()).toBe(false);
        expect(view.get('[data-testid="army-list-panel-10"]').text()).toContain('Alan Turing');
        expect(view.get('[data-testid="army-list-panel-10"]').text()).toContain('Katherine Johnson');
    });

    it('moves between the tabs on the arrow keys, wrapping rather than dead-ending', async () => {
        stubEverything();

        const view = await mountGame();

        const tabs = view.get('[data-testid="army-list-tabs"]');

        await tabs.trigger('keydown', { key: 'ArrowLeft' });
        expect(view.get('[data-testid="army-list-tab-10"]').attributes('aria-selected')).toBe('true');

        await tabs.trigger('keydown', { key: 'ArrowRight' });
        expect(view.get('[data-testid="army-list-tab-9"]').attributes('aria-selected')).toBe('true');

        await tabs.trigger('keydown', { key: 'End' });
        expect(view.get('[data-testid="army-list-tab-10"]').attributes('aria-selected')).toBe('true');
    });

    it('says a list is closed rather than leaving a blank that reads as none', async () => {
        stubEverything();

        const view = await mountGame();

        await view.get('[data-testid="army-list-tab-10"]').trigger('click');

        expect(view.find('[data-testid="army-list-14"]').exists()).toBe(false);
        expect(view.get('[data-testid="army-list-closed-14"]').text())
            .toBe('This list is in, and opens once every player on the team has submitted.');
        expect(view.get('[data-testid="army-list-closed-15"]').text()).toBe('This list has not been submitted yet.');
    });

    it('answers a game in a draft round the same way as one that does not exist', async () => {
        stubEverything({ [`/api/events/${EVENT_SLUG}/games/18`]: NOT_FOUND });

        const view = await mountGame();

        expect(view.text()).toContain('We could not find that game.');
        expect(view.text()).not.toContain('draft');
    });
});
