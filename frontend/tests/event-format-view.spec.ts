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
                abbreviation: 'MP',
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
                abbreviation: 'VP',
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

/** Columns arrive folded, so a test that edits one opens it first. */
async function expand(view: ReturnType<typeof mountView>, index: number): Promise<void> {
    await view.get(`[data-testid="score-toggle-${index}"]`).trigger('click');
}

function valueOf(view: ReturnType<typeof mountView>, testid: string): string {
    return view.get<HTMLInputElement | HTMLSelectElement>(`[data-testid="${testid}"]`).element.value;
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
    it('leads back to the organise hub it hangs off', async () => {
        stubApi({ [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() } });

        const view = mountView();
        await flushPromises();

        const back = view.get('[data-testid="back-to-organise"]');

        expect(back.attributes('href')).toBe(`/events/${EVENT_SLUG}/organise`);
    });

    it('opens on the shape the event is already run at', async () => {
        stubApi({ [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() } });

        const view = mountView();
        await flushPromises();

        expect(view.text()).toContain('Event format');
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

    it('opens the scoring on the columns the event already has, in the order they are shown', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`/api/events/${EVENT_SLUG}/score-types`]: { status: 200, body: scoreTypesBody() },
        });

        const view = mountView();
        await flushPromises();

        expect(view.findAll('[data-testid="format-score-type"]')).toHaveLength(2);

        // Folded, each column is its name and how it is scored; the fields
        // are behind the toggle.
        expect(view.find('[data-testid="score-name-0"]').exists()).toBe(false);
        expect(view.get('[data-testid="score-toggle-0"]').text()).toContain('Match Points');

        await expand(view, 0);
        await expand(view, 1);

        expect(valueOf(view, 'score-name-0')).toBe('Match Points');
        expect(valueOf(view, 'score-source-0')).toBe('derived');
        expect(valueOf(view, 'score-direction-0')).toBe('desc');
        expect(valueOf(view, 'score-win-0')).toBe('3.00');
        expect(view.get('[data-testid="score-ranking-0"]').attributes('aria-pressed')).toBe('true');

        expect(valueOf(view, 'score-name-1')).toBe('Victory Points');
        expect(valueOf(view, 'score-source-1')).toBe('entered');
        expect(valueOf(view, 'score-direction-1')).toBe('asc');
        expect(view.find('[data-testid="score-win-1"]').exists()).toBe(false);
        expect(view.get('[data-testid="score-primary-1"]').attributes('aria-pressed')).toBe('true');
        expect(view.get('[data-testid="score-ranking-1"]').attributes('aria-pressed')).toBe('false');
    });

    it('says so when the event is scored on nothing at all', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`/api/events/${EVENT_SLUG}/score-types`]: { status: 200, body: { data: [] } },
        });

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="format-no-score-types"]').text()).toContain('No scoring set up yet');

        // An Event with nothing to score on still has to be able to gain a
        // column, so the add control is not hidden with the list.
        expect(view.find('[data-testid="score-add"]').exists()).toBe(true);
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

    it('counts teams rather than attendees once the event is played in teams', async () => {
        stubApi({
            [`GET /api/events/${EVENT_SLUG}`]: {
                status: 200,
                body: eventBody(true, { attendees_count: 0, attendee_size: 1 }),
            },
        });

        const view = mountView();
        await flushPromises();

        expect(view.text()).toContain('Max Attendees');

        await view.get('[data-testid="format-attendee-size"]').setValue('2');

        expect(view.text()).toContain('Max Teams');
        expect(view.text()).not.toContain('Max Attendees');
    });

    it('locks the shape of an event somebody has already entered, and says why', async () => {
        stubApi({ [`GET /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() } });

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="format-game-system"]').attributes('disabled')).toBeDefined();
        expect(view.get('[data-testid="format-attendee-size"]').attributes('disabled')).toBeDefined();
        expect(view.get('[data-testid="format-shape-locked"]').text())
            .toContain('Game system and team size are now fixed.');
    });

    it('sends the heading an organiser wrote for a column, and shows it folded', async () => {
        const fetch = stubApi({
            [`GET /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`GET /api/events/${EVENT_SLUG}/score-types`]: { status: 200, body: scoreTypesBody() },
            [`PUT /api/events/${EVENT_SLUG}/score-types`]: { status: 200, body: scoreTypesBody() },
        });

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="score-toggle-0"]').text()).toContain('MP');

        await expand(view, 0);

        expect(valueOf(view, 'score-abbreviation-0')).toBe('MP');

        await view.get('[data-testid="score-abbreviation-0"]').setValue('MPs');
        await view.get('[data-testid="format-scoring-save"]').trigger('submit');
        await flushPromises();

        const put = fetch.mock.calls.find(([, init]) => (init as RequestInit | undefined)?.method === 'PUT');
        const sent = JSON.parse(String((put?.[1] as RequestInit).body)) as {
            score_types: { abbreviation: string }[];
        };

        expect(sent.score_types[0]?.abbreviation).toBe('MPs');
    });

    it('leaves a blank heading to the api, which works one out from the name', async () => {
        const fetch = stubApi({
            [`GET /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`GET /api/events/${EVENT_SLUG}/score-types`]: { status: 200, body: scoreTypesBody() },
            [`PUT /api/events/${EVENT_SLUG}/score-types`]: { status: 200, body: scoreTypesBody() },
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="score-add"]').trigger('click');
        await view.get('[data-testid="score-name-2"]').setValue('Painting Score');
        await view.get('[data-testid="format-scoring-save"]').trigger('submit');
        await flushPromises();

        const put = fetch.mock.calls.find(([, init]) => (init as RequestInit | undefined)?.method === 'PUT');
        const sent = JSON.parse(String((put?.[1] as RequestInit).body)) as {
            score_types: { abbreviation: string }[];
        };

        expect(sent.score_types[2]?.abbreviation).toBe('');
    });

    it('edits a score type and sends the whole ordered set on its own button', async () => {
        const fetch = stubApi({
            [`GET /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`GET /api/events/${EVENT_SLUG}/score-types`]: { status: 200, body: scoreTypesBody() },
            [`PUT /api/events/${EVENT_SLUG}/score-types`]: { status: 200, body: scoreTypesBody() },
        });

        const view = mountView();
        await flushPromises();

        await expand(view, 0);
        await view.get('[data-testid="score-name-0"]').setValue('League Points');
        await view.get('[data-testid="score-down-0"]').trigger('click');
        await view.get('[data-testid="format-scoring-save"]').trigger('submit');
        await flushPromises();

        const put = fetch.mock.calls.find(([, init]) => (init as RequestInit | undefined)?.method === 'PUT');
        const sent = JSON.parse(String((put?.[1] as RequestInit).body)) as {
            score_types: { id: number; name: string; counts_for_ranking: boolean }[];
        };

        // Victory Points was moved above the renamed row, and the whole set
        // travels in the order it is now shown.
        expect(sent.score_types.map((row) => row.id)).toEqual([8, 7]);
        expect(sent.score_types[1]?.name).toBe('League Points');
        expect(view.get('[data-testid="format-scoring-saved"]').text()).toContain('Saved');
    });

    it('saves the scoring without the event fields above it', async () => {
        const fetch = stubApi({
            [`GET /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`GET /api/events/${EVENT_SLUG}/score-types`]: { status: 200, body: scoreTypesBody() },
            [`PUT /api/events/${EVENT_SLUG}/score-types`]: { status: 200, body: scoreTypesBody() },
        });

        const view = mountView();
        await flushPromises();

        // The places field is left half-filled: saving the scoring must not
        // depend on it.
        await view.get('[data-testid="format-max-attendees"]').setValue('');
        await expand(view, 1);
        await view.get('[data-testid="score-ranking-1"]').trigger('click');
        await view.get('[data-testid="format-scoring-save"]').trigger('submit');
        await flushPromises();

        const methods = fetch.mock.calls.map(([, init]) => (init as RequestInit | undefined)?.method ?? 'GET');
        const put = fetch.mock.calls.find(([, init]) => (init as RequestInit | undefined)?.method === 'PUT');
        const sent = JSON.parse(String((put?.[1] as RequestInit).body)) as {
            score_types: { counts_for_ranking: boolean }[];
        };

        expect(methods).not.toContain('PATCH');
        expect(sent.score_types[1]?.counts_for_ranking).toBe(true);
    });

    it('opens one column and leaves the rest of the set folded', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`/api/events/${EVENT_SLUG}/score-types`]: { status: 200, body: scoreTypesBody() },
        });

        const view = mountView();
        await flushPromises();

        expect(view.find('[data-testid="score-name-0"]').exists()).toBe(false);
        expect(view.get('[data-testid="score-toggle-0"]').attributes('aria-expanded')).toBe('false');
        // Folded, it still says which column it is and how it is scored.
        expect(view.get('[data-testid="score-toggle-0"]').text()).toContain('Match Points');
        expect(view.get('[data-testid="score-toggle-0"]').text()).toContain('Calculated from the result · higher to lower');

        await expand(view, 0);

        expect(view.find('[data-testid="score-name-0"]').exists()).toBe(true);
        expect(view.get('[data-testid="score-toggle-0"]').attributes('aria-expanded')).toBe('true');
        expect(view.find('[data-testid="score-name-1"]').exists()).toBe(false);

        await expand(view, 0);

        expect(view.find('[data-testid="score-name-0"]').exists()).toBe(false);
    });

    it('offers how a column is scored and ordered in the words an organiser uses', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`/api/events/${EVENT_SLUG}/score-types`]: { status: 200, body: scoreTypesBody() },
        });

        const view = mountView();
        await flushPromises();
        await expand(view, 0);

        const labels = (testid: string) => view.findAll(`[data-testid="${testid}"] option`)
            .map((option) => option.text())
            .filter((label) => label !== 'Choose one');

        expect(labels('score-source-0')).toEqual(['Game score', 'Calculated from the result']);
        expect(labels('score-direction-0')).toEqual(['Higher to lower', 'Lower to higher']);
        expect(view.text()).not.toContain('Shown over the column');
    });

    it('opens a column it has just added, since a blank card has nothing to read', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`/api/events/${EVENT_SLUG}/score-types`]: { status: 200, body: scoreTypesBody() },
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="score-add"]').trigger('click');

        expect(view.find('[data-testid="score-name-2"]').exists()).toBe(true);
    });

    it('unfolds a folded column the api rejected, so the reason is not hidden', async () => {
        stubApi({
            [`GET /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`GET /api/events/${EVENT_SLUG}/score-types`]: { status: 200, body: scoreTypesBody() },
            [`PUT /api/events/${EVENT_SLUG}/score-types`]: {
                status: 422,
                body: {
                    message: 'The given data was invalid.',
                    errors: { 'score_types.1.name': ['A column needs a name.'] },
                },
            },
        });

        const view = mountView();
        await flushPromises();

        await expand(view, 1);
        await view.get('[data-testid="score-name-1"]').setValue('');
        await expand(view, 1);

        expect(view.find('[data-testid="score-name-1"]').exists()).toBe(false);

        await view.get('[data-testid="format-scoring-save"]').trigger('submit');
        await flushPromises();

        expect(view.find('[data-testid="score-name-1"]').exists()).toBe(true);
        expect(view.get('[data-testid="score-name-1-error"]').text()).toContain('A column needs a name.');
    });

    it('puts a rejected row under the row it belongs to', async () => {
        stubApi({
            [`GET /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`GET /api/events/${EVENT_SLUG}/score-types`]: { status: 200, body: scoreTypesBody() },
            [`PUT /api/events/${EVENT_SLUG}/score-types`]: {
                status: 422,
                body: {
                    message: 'The given data was invalid.',
                    errors: { 'score_types.1.name': ['A column needs a name.'] },
                },
            },
        });

        const view = mountView();
        await flushPromises();

        await expand(view, 1);
        await view.get('[data-testid="score-name-1"]').setValue('');
        await view.get('[data-testid="format-scoring-save"]').trigger('submit');
        await flushPromises();

        expect(view.get('[data-testid="score-name-1-error"]').text()).toContain('A column needs a name.');
        expect(view.find('[data-testid="score-name-0-error"]').exists()).toBe(false);
    });

    it('adds a column the event did not have, and sends it without an id', async () => {
        const fetch = stubApi({
            [`GET /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`GET /api/events/${EVENT_SLUG}/score-types`]: { status: 200, body: scoreTypesBody() },
            [`PUT /api/events/${EVENT_SLUG}/score-types`]: { status: 200, body: scoreTypesBody() },
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="score-add"]').trigger('click');
        await view.get('[data-testid="score-name-2"]').setValue('Painting');
        await view.get('[data-testid="format-scoring-save"]').trigger('submit');
        await flushPromises();

        const put = fetch.mock.calls.find(([, init]) => (init as RequestInit | undefined)?.method === 'PUT');
        const sent = JSON.parse(String((put?.[1] as RequestInit).body)) as {
            score_types: { id?: number | null; name: string }[];
        };

        expect(sent.score_types).toHaveLength(3);
        expect(sent.score_types[2]?.name).toBe('Painting');
        expect(sent.score_types[2]?.id ?? null).toBeNull();
    });

    it('removes a column nobody has been scored on, and will not remove one that has', async () => {
        const fetch = stubApi({
            [`GET /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`GET /api/events/${EVENT_SLUG}/score-types`]: { status: 200, body: scoreTypesBody() },
            [`PUT /api/events/${EVENT_SLUG}/score-types`]: { status: 200, body: scoreTypesBody() },
        });

        const view = mountView();
        await flushPromises();

        // Match Points has been scored on: its remove control is dead, and
        // says why.
        expect(view.get('[data-testid="score-remove-0"]').attributes('disabled')).toBeDefined();

        await expand(view, 0);

        expect(view.get('[data-testid="format-score-type"]').text())
            .toContain('Games have already been scored on this column');

        await view.get('[data-testid="score-remove-1"]').trigger('click');
        await view.get('[data-testid="format-scoring-save"]').trigger('submit');
        await flushPromises();

        const put = fetch.mock.calls.find(([, init]) => (init as RequestInit | undefined)?.method === 'PUT');
        const sent = JSON.parse(String((put?.[1] as RequestInit).body)) as { score_types: { id: number }[] };

        expect(sent.score_types.map((row) => row.id)).toEqual([7]);
    });
});
