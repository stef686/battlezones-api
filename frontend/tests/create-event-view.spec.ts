import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { createAppRouter } from '@/router';
import { useSessionStore } from '@/stores/session';
import { useToastStore } from '@/stores/toast';
import CreateEventView from '@/views/CreateEventView.vue';

const GAME_SYSTEMS = {
    status: 200,
    body: { data: [{ id: 4, name: 'Horus Heresy', slug: 'horus-heresy' }] },
};

const COUNTRIES = { status: 200, body: { data: [{ code: 'GB', name: 'United Kingdom' }] } };

function createdBody() {
    return {
        data: {
            id: 9,
            name: 'Croydon Doubles',
            slug: 'croydon-doubles',
            description: null,
            status: 'draft',
            starts_at: '2027-03-06T09:00:00Z',
            ends_at: '2027-03-06T18:00:00Z',
            max_attendees: null,
            attendee_size: 2,
            requires_allegiance: false,
            timezone: 'Europe/London',
            registration_closes_at: null,
            attendees_count: 0,
            is_full: false,
            game_system: { id: 4, name: 'Horus Heresy', slug: 'horus-heresy' },
            venue: { name: null, address: null, city: null, country: null },
            banner: null,
            documents: [],
            viewer: {
                is_organiser: true,
                is_lead_organiser: true,
                is_attendee: false,
                attendee_id: null,
                permissions: { organise: true, register: true, manage_organisers: true },
            },
        },
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

function bodyOf(fetch: ReturnType<typeof stubApi>, method: string): Record<string, unknown> | undefined {
    const call = fetch.mock.calls.find(([, init]) => (init as RequestInit | undefined)?.method === method);

    return call === undefined ? undefined : JSON.parse(String((call[1] as RequestInit).body)) as Record<string, unknown>;
}

let router: Router;
let pinia: ReturnType<typeof createPinia>;
let queryClient: QueryClient;

function mountView() {
    // Attached, because a button only submits the form it sits in once both
    // are in a document — which is how Next and Create reach the wizard.
    return mount(CreateEventView, {
        attachTo: document.body,
        global: { plugins: [pinia, router, [VueQueryPlugin, { queryClient }]] },
    });
}

type View = ReturnType<typeof mountView>;

/** A start safely in the future, as a datetime-local input holds it. */
const STARTS = '2027-03-06T09:00';
const ENDS = '2027-03-06T18:00';

async function fillDetails(view: View): Promise<void> {
    await view.get('[data-testid="event-name"]').setValue('Croydon Doubles');
    await view.get('[data-testid="create-slug"]').setValue('croydon-doubles');
    await view.get('[data-testid="event-starts-at"]').setValue(STARTS);
    await view.get('[data-testid="event-ends-at"]').setValue(ENDS);
}

async function next(view: View): Promise<void> {
    await view.get('[data-testid="create-next"]').trigger('click');
    await flushPromises();
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

    useSessionStore().viewer = {
        id: 4,
        public_name: 'Ada Lovelace',
        email: 'ada@example.test',
        is_claimed: true,
        email_verified: true,
        unread_notifications_count: 0,
    };

    vi.spyOn(Intl.DateTimeFormat.prototype, 'resolvedOptions')
        .mockReturnValue({ timeZone: 'Europe/London' } as Intl.ResolvedDateTimeFormatOptions);

    await router.push('/events/new');
    await router.isReady();
});

afterEach(() => {
    document.body.innerHTML = '';
    vi.restoreAllMocks();
    vi.unstubAllGlobals();
});

describe('creating an event', () => {
    it('steps through details, format and confirm, then lands the organiser on the event they now lead', async () => {
        const fetch = stubApi({
            '/api/game-systems': GAME_SYSTEMS,
            '/api/countries': COUNTRIES,
            'POST /api/events': { status: 201, body: createdBody() },
        });

        const view = mountView();
        await flushPromises();

        await fillDetails(view);
        await next(view);

        await view.get('[data-testid="event-game-system"]').setValue('4');
        await view.get('[data-testid="event-attendee-size"]').setValue('2');
        await next(view);

        expect(view.get('[data-testid="create-summary"]').text()).toContain('Croydon Doubles');

        await view.get('[data-testid="create-submit"]').trigger('click');
        await flushPromises();

        expect(bodyOf(fetch, 'POST')).toMatchObject({
            name: 'Croydon Doubles',
            slug: 'croydon-doubles',
            starts_at: '2027-03-06T09:00:00+00:00',
            ends_at: '2027-03-06T18:00:00+00:00',
            timezone: 'Europe/London',
            game_system_id: 4,
            attendee_size: 2,
            max_attendees: null,
            venue_name: null,
        });
        // The hub is a lazy route, so landing on it outlasts one flush.
        await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('organise'));
        expect(router.currentRoute.value.params.eventSlug).toBe('croydon-doubles');
        expect(useToastStore().message).toBe('Event created');
    });

    it('will not move on from a step until what it asks for is there, and says what is missing', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        await next(view);

        expect(view.get('[data-testid="create-step-details"]').attributes('aria-current')).toBe('step');
        expect(view.get('[data-testid="event-name-error"]').text()).toContain('name');
        expect(view.get('[data-testid="create-slug-error"]').text()).toContain('URL');

        await fillDetails(view);
        await next(view);
        await next(view);

        expect(view.get('[data-testid="create-step-format"]').attributes('aria-current')).toBe('step');
        expect(view.get('[data-testid="event-game-system-error"]').text()).toContain('game system');
    });

    it.each([
        ['croydon--doubles', '3 to 60'],
        ['-croydon', '3 to 60'],
        ['croydon-', '3 to 60'],
        ['new', 'reserved'],
    ])('refuses the url %s before the server has to', async (slug, reason) => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        await fillDetails(view);
        await view.get('[data-testid="create-slug"]').setValue(slug);
        await next(view);

        expect(view.get('[data-testid="create-step-details"]').attributes('aria-current')).toBe('step');
        expect(view.get('[data-testid="create-slug-error"]').text()).toContain(reason);
    });

    it('refuses a start that has already been, before the server has to', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        await fillDetails(view);
        await view.get('[data-testid="event-starts-at"]').setValue('2020-01-01T09:00');
        await next(view);

        expect(view.get('[data-testid="event-starts-at-error"]').text()).toContain('past');
    });

    it('keeps what was typed when the organiser goes back a step', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        await fillDetails(view);
        await view.get('[data-testid="event-venue-city"]').setValue('Croydon');
        await next(view);
        await view.get('[data-testid="create-back"]').trigger('click');
        await flushPromises();

        expect(view.get<HTMLInputElement>('[data-testid="event-name"]').element.value).toBe('Croydon Doubles');
        expect(view.get<HTMLInputElement>('[data-testid="event-venue-city"]').element.value).toBe('Croydon');
    });

    it('offers the scoring most events use, and sends it as the organiser left it', async () => {
        const fetch = stubApi({
            '/api/game-systems': GAME_SYSTEMS,
            '/api/countries': COUNTRIES,
            'POST /api/events': { status: 201, body: createdBody() },
        });

        const view = mountView();
        await flushPromises();

        await fillDetails(view);
        await next(view);

        expect(view.findAll('[data-testid="score-type"]').map((row) => row.text()))
            .toEqual([expect.stringContaining('Match Points'), expect.stringContaining('Victory Points')]);

        await view.get('[data-testid="event-game-system"]').setValue('4');
        await next(view);
        await view.get('[data-testid="create-submit"]').trigger('click');
        await flushPromises();

        expect(bodyOf(fetch, 'POST')?.score_types).toEqual([
            expect.objectContaining({
                name: 'Match Points',
                abbreviation: 'MP',
                is_derived: true,
                is_primary: true,
                counts_for_ranking: true,
                win_points: 3,
                draw_points: 1,
                loss_points: 0,
            }),
            expect.objectContaining({
                name: 'Victory Points',
                abbreviation: 'VP',
                is_derived: false,
                is_primary: false,
                counts_for_ranking: true,
                sort_direction: 'desc',
            }),
        ]);
    });

    it('sends the organiser back to the step a refused field is on, with the reason under it', async () => {
        stubApi({
            '/api/game-systems': GAME_SYSTEMS,
            '/api/countries': COUNTRIES,
            'POST /api/events': {
                status: 422,
                body: { message: 'Invalid.', errors: { slug: ['That URL is already taken.'] } },
            },
        });

        const view = mountView();
        await flushPromises();

        await fillDetails(view);
        await next(view);
        await view.get('[data-testid="event-game-system"]').setValue('4');
        await next(view);
        await view.get('[data-testid="create-submit"]').trigger('click');
        await flushPromises();

        expect(view.get('[data-testid="create-step-details"]').attributes('aria-current')).toBe('step');
        expect(view.get('[data-testid="create-slug-error"]').text()).toBe('That URL is already taken.');
        expect(router.currentRoute.value.name).toBe('create-event');
    });

    it('puts a refused score under its own row on the format step', async () => {
        stubApi({
            '/api/game-systems': GAME_SYSTEMS,
            '/api/countries': COUNTRIES,
            'POST /api/events': {
                status: 422,
                body: { message: 'Invalid.', errors: { 'score_types.1.name': ['That name is too long.'] } },
            },
        });

        const view = mountView();
        await flushPromises();

        await fillDetails(view);
        await next(view);
        await view.get('[data-testid="event-game-system"]').setValue('4');
        await next(view);
        await view.get('[data-testid="create-submit"]').trigger('click');
        await flushPromises();

        expect(view.get('[data-testid="create-step-format"]').attributes('aria-current')).toBe('step');
        expect(view.get('[data-testid="score-name-1-error"]').text()).toBe('That name is too long.');
    });

    it('says what was refused above the form when no step has a field to show it under', async () => {
        stubApi({
            '/api/game-systems': GAME_SYSTEMS,
            '/api/countries': COUNTRIES,
            'POST /api/events': {
                status: 422,
                body: { message: 'The status field is prohibited.', errors: { status: ['The status field is prohibited.'] } },
            },
        });

        const view = mountView();
        await flushPromises();

        await fillDetails(view);
        await next(view);
        await view.get('[data-testid="event-game-system"]').setValue('4');
        await next(view);
        await view.get('[data-testid="create-submit"]').trigger('click');
        await flushPromises();

        expect(view.get('[data-testid="create-problem"]').text()).toBe('The status field is prohibited.');
    });
});
