import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { createAppRouter } from '@/router';
import StandingsView from '@/views/StandingsView.vue';

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

function scored(match: string, victory: string) {
    return [
        { value: match, score_type: { slug: 'match-points', name: 'Match Points' } },
        { value: victory, score_type: { slug: 'victory-points', name: 'Victory Points' } },
    ];
}

const STANDINGS = {
    data: [
        { id: 1, position: 1, attendee: { id: 9, name: 'Sons of Terra' }, scores: scored('6.00', '170.50') },
        { id: 2, position: 2, attendee: { id: 10, name: 'The Warmaster\'s Own' }, scores: scored('3.00', '150.00') },
        { id: 3, position: 3, attendee: { id: 11, name: 'Terran Reserve' }, scores: [] },
    ],
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

let router: Router;
let pinia: ReturnType<typeof createPinia>;
let queryClient: QueryClient;

async function mountStandings(standings: unknown = STANDINGS) {
    stubApi({
        [`/api/events/${EVENT_SLUG}/standings`]: { status: 200, body: standings },
        [`/api/events/${EVENT_SLUG}/pulse`]: { status: 200, body: PULSE },
        [`/api/events/${EVENT_SLUG}`]: { status: 200, body: EVENT },
    });

    const view = mount(StandingsView as never, ({
        props: { eventSlug: EVENT_SLUG },
        global: { plugins: [pinia, router, [VueQueryPlugin, { queryClient }]] },
    }) as never);

    await flushPromises();

    return view;
}

function names(view: Awaited<ReturnType<typeof mountStandings>>): string[] {
    return view.findAll('tbody tr').map((row) => row.get('th[scope="row"]').text());
}

beforeEach(async () => {
    window.localStorage.clear();
    pinia = createPinia();
    setActivePinia(pinia);

    queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });

    router = createAppRouter();
    createApiClient(router, { baseUrl: 'https://api.test', storage: new InMemoryTokenStorage() });

    await router.push(`/events/${EVENT_SLUG}/standings`);
    await router.isReady();
});

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('the standings', () => {
    it('ranks every team, with the score it has under each column', async () => {
        const view = await mountStandings();

        expect(names(view)).toEqual(['Sons of Terra', 'The Warmaster\'s Own', 'Terran Reserve']);

        const leader = view.get('[data-testid="standing-9"]');

        expect(leader.get('[data-testid="match-points"]').text()).toBe('6');
        expect(leader.get('[data-testid="victory-points"]').text()).toBe('170.5');
        // A team with nothing scored yet is dashed rather than left blank.
        expect(view.get('[data-testid="standing-11"] [data-testid="match-points"]').text()).toBe('—');
    });

    it('opens a team from its row, rather than sending a reader round by the attendees tab', async () => {
        const view = await mountStandings();

        const link = view.get('[data-testid="open-attendee-10"]');

        expect(link.attributes('href')).toBe(`/events/${EVENT_SLUG}/attendees/10`);
        expect(link.text()).toBe('The Warmaster\'s Own');
    });

    it('filters the table down to a team the reader is looking for', async () => {
        const view = await mountStandings();

        await view.get('[data-testid="standings-search"]').setValue('terra');

        // Matched on the name the rows show, wherever the term falls in it.
        expect(names(view)).toEqual(['Sons of Terra', 'Terran Reserve']);
    });

    it('spends no line on a label, and searches from the placeholder alone', async () => {
        const view = await mountStandings();

        const field = view.get('[data-testid="standings-search"]');

        expect(field.attributes('placeholder')).toBe('Search by team name…');
        // The label is still in the markup, just not on screen: an input with
        // no accessible name says nothing to a screen reader.
        expect(view.get(`label[for="${field.attributes('id')}"]`).classes()).toContain('sr-only');
    });

    it('says nothing matched rather than reading as an event nobody has scored in', async () => {
        const view = await mountStandings();

        await view.get('[data-testid="standings-search"]').setValue('nobody');

        expect(view.find('[data-testid="standings"]').exists()).toBe(false);
        expect(view.get('[data-testid="standings-unmatched"]').text())
            .toBe('No team in this event matches “nobody”.');
    });

    it('offers no search where there is nothing to search through', async () => {
        const view = await mountStandings({ data: [] });

        expect(view.find('[data-testid="standings-search"]').exists()).toBe(false);
        expect(view.find('[data-testid="standings-unmatched"]').exists()).toBe(false);
    });
});
