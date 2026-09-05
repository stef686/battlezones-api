import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { createAppRouter } from '@/router';
import EventFormatView from '@/views/EventFormatView.vue';

const EVENT_SLUG = 'london-grand-tournament';

function eventBody(organise = true, overrides: Record<string, unknown> = {}) {
    return {
        data: {
            id: 1,
            name: 'London Grand Tournament',
            slug: EVENT_SLUG,
            description: 'A two-day Horus Heresy doubles event.',
            status: 'published',
            starts_at: '2026-09-12T09:00:00Z',
            ends_at: '2026-09-13T18:00:00Z',
            max_attendees: 32,
            attendee_size: 2,
            requires_allegiance: true,
            registration_closes_at: null,
            attendees_count: 18,
            is_full: false,
            game_system: { id: 4, name: 'Horus Heresy', slug: 'horus-heresy' },
            venue: { name: 'The Hall', address: '1 Example Street', city: 'London', country: 'GB' },
            banner: null,
            documents: [],
            viewer: {
                is_organiser: organise,
                is_lead_organiser: organise,
                is_attendee: false,
                attendee_id: null,
                permissions: { organise, register: false, manage_organisers: organise },
            },
            ...overrides,
        },
    };
}

function scoreTypesBody() {
    return {
        data: [
            {
                id: 7,
                name: 'Match Points',
                slug: 'match-points',
                sort_direction: 'desc',
                is_derived: true,
                is_primary: false,
                counts_for_ranking: true,
                ranking_order: 1,
                win_points: '3.00',
                draw_points: '1.00',
                loss_points: '0.00',
                display_order: 0,
                is_scored: true,
            },
            {
                id: 8,
                name: 'Victory Points',
                slug: 'victory-points',
                sort_direction: 'asc',
                is_derived: false,
                is_primary: true,
                counts_for_ranking: false,
                ranking_order: null,
                win_points: null,
                draw_points: null,
                loss_points: null,
                display_order: 1,
                is_scored: false,
            },
        ],
    };
}

function gameSystemsBody() {
    return {
        data: [
            { id: 4, name: 'Horus Heresy', slug: 'horus-heresy' },
            { id: 1, name: 'Warhammer 40,000', slug: 'warhammer-40000' },
        ],
    };
}

const NOT_FOUND = { status: 404, body: { message: 'Not Found.' } };

function stubApi(routes: Record<string, { status: number; body?: unknown }>) {
    const fetch = vi.fn((url: string, init?: RequestInit) => {
        const path = String(url).replace('https://api.test', '').split('?')[0] ?? '';
        const method = (init?.method ?? 'GET').toUpperCase();
        const match = Object.entries(routes).find(([pattern]) => {
            const [verb, endpoint] = pattern.includes(' ') ? pattern.split(' ') : ['GET', pattern];

            return verb === method && path.endsWith(String(endpoint));
        });
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

function valueOf(view: ReturnType<typeof mountView>, testid: string): string {
    return view.get<HTMLInputElement>(`[data-testid="${testid}"]`).element.value;
}

let router: Router;
let pinia: ReturnType<typeof createPinia>;
let queryClient: QueryClient;

function mountView() {
    return mount(EventFormatView as never, ({
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

    await router.push(`/events/${EVENT_SLUG}/organise/format`);
    await router.isReady();
});

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('the event format screen', () => {
    it('opens on the shape the event is already run at', async () => {
        stubApi({ [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() } });

        const view = mountView();
        await flushPromises();

        expect(view.text()).toContain('London Grand Tournament');
        expect(valueOf(view, 'format-max-attendees')).toBe('32');
    });

    it('sends only what the organiser changed', async () => {
        const fetch = stubApi({
            [`GET /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`PATCH /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody(true, { max_attendees: 24 }) },
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="format-max-attendees"]').setValue('24');
        await view.get('[data-testid="format-save"]').trigger('submit');
        await flushPromises();

        const patch = fetch.mock.calls.find(([, init]) => (init as RequestInit | undefined)?.method === 'PATCH');

        expect(patch).toBeDefined();
        expect(JSON.parse(String((patch?.[1] as RequestInit).body))).toEqual({ max_attendees: 24 });
        expect(view.get('[data-testid="format-saved"]').text()).toContain('Saved');
    });

    it('clears the limit when the places field is emptied', async () => {
        const fetch = stubApi({
            [`GET /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`PATCH /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody(true, { max_attendees: null }) },
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="format-max-attendees"]').setValue('');
        await view.get('[data-testid="format-save"]').trigger('submit');
        await flushPromises();

        const patch = fetch.mock.calls.find(([, init]) => (init as RequestInit | undefined)?.method === 'PATCH');

        expect(JSON.parse(String((patch?.[1] as RequestInit).body))).toEqual({ max_attendees: null });
    });

    it('will not save until something has changed', async () => {
        stubApi({ [`GET /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() } });

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="format-save"] button').attributes('disabled')).toBeDefined();

        await view.get('[data-testid="format-max-attendees"]').setValue('24');

        expect(view.get('[data-testid="format-save"] button').attributes('disabled')).toBeUndefined();
    });

    it('puts a rejected places limit under the places field', async () => {
        stubApi({
            [`GET /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`PATCH /api/events/${EVENT_SLUG}`]: {
                status: 422,
                body: {
                    message: 'The given data was invalid.',
                    errors: { max_attendees: ['There are already 18 parties entered.'] },
                },
            },
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="format-max-attendees"]').setValue('2');
        await view.get('[data-testid="format-save"]').trigger('submit');
        await flushPromises();

        expect(view.get('[data-testid="format-max-attendees-error"]').text())
            .toContain('There are already 18 parties entered.');
    });

    it('is not there at all for a reader without the permission', async () => {
        stubApi({ [`GET /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody(false) } });

        const view = mountView();
        await flushPromises();

        // The same answer as an Event that does not exist: whether someone
        // else's Event has an organiser screen is not this reader's business.
        expect(view.get('[data-testid="missing"]').text()).toContain('Not found');
        expect(view.find('[data-testid="format-max-attendees"]').exists()).toBe(false);
    });

    it('lists the score types the event is ranked on, in the order they are shown', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`/api/events/${EVENT_SLUG}/score-types`]: { status: 200, body: scoreTypesBody() },
        });

        const view = mountView();
        await flushPromises();

        const rows = view.findAll('[data-testid="format-score-type"]');

        expect(rows).toHaveLength(2);
        expect(rows[0]?.text()).toContain('Match Points');
        expect(rows[0]?.text()).toContain('Worked out for them');
        expect(rows[0]?.text()).toContain('Higher is better');
        expect(rows[0]?.text()).toContain('Counts for ranking');
        expect(rows[1]?.text()).toContain('Victory Points');
        expect(rows[1]?.text()).toContain('Entered by players');
        expect(rows[1]?.text()).toContain('Lower is better');
        expect(rows[1]?.text()).toContain('Leads a game listing');
        expect(rows[1]?.text()).not.toContain('Counts for ranking');
    });

    it('says so when the event is scored on nothing at all', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`/api/events/${EVENT_SLUG}/score-types`]: { status: 200, body: { data: [] } },
        });

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="format-no-score-types"]').text()).toContain('No scoring set up yet');
    });

    it('lets an organiser reshape an event nobody has entered yet', async () => {
        const fetch = stubApi({
            [`GET /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody(true, { attendees_count: 0 }) },
            '/api/game-systems': { status: 200, body: gameSystemsBody() },
            [`PATCH /api/events/${EVENT_SLUG}`]: {
                status: 200,
                body: eventBody(true, { attendees_count: 0, attendee_size: 1 }),
            },
        });

        const view = mountView();
        await flushPromises();

        expect(valueOf(view, 'format-game-system')).toBe('4');
        expect(valueOf(view, 'format-attendee-size')).toBe('2');

        await view.get('[data-testid="format-game-system"]').setValue('1');
        await view.get('[data-testid="format-attendee-size"]').setValue('1');
        await view.get('[data-testid="format-save"]').trigger('submit');
        await flushPromises();

        const patch = fetch.mock.calls.find(([, init]) => (init as RequestInit | undefined)?.method === 'PATCH');

        expect(JSON.parse(String((patch?.[1] as RequestInit).body)))
            .toEqual({ game_system_id: 1, attendee_size: 1 });
    });

    it('locks the shape of an event somebody has already entered, and says why', async () => {
        stubApi({ [`GET /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() } });

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="format-game-system"]').attributes('disabled')).toBeDefined();
        expect(view.get('[data-testid="format-attendee-size"]').attributes('disabled')).toBeDefined();
        expect(view.get('[data-testid="format-shape-locked"]').text())
            .toContain('18 parties have entered');
    });
});
