import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { defineComponent } from 'vue';
import { RouterView, type Router } from 'vue-router';

import { createApiClient, useApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { createAppRouter } from '@/router';
import { useSessionStore } from '@/stores/session';
import { useToastStore } from '@/stores/toast';

import { fakeImages } from './fake-images';

const GAME_SYSTEMS = {
    status: 200,
    body: { data: [{ id: 4, name: 'Horus Heresy', slug: 'horus-heresy' }] },
};

const COUNTRIES = { status: 200, body: { data: [{ code: 'GB', name: 'United Kingdom' }] } };

const TIMEZONES = { status: 200, body: { data: ['America/New_York', 'Asia/Kolkata', 'Europe/London'] } };

function createdBody(overrides: Record<string, unknown> = {}) {
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
            ...overrides,
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

/**
 * Every wizard a spec drew, unmounted before the mocks it leans on are put
 * back: one left mounted keeps its beforeunload listener on the window.
 */
const mounted: { unmount: () => void }[] = [];

function mountView() {
    // Attached, because a button only submits the form it sits in once both
    // are in a document — which is how Next and Create reach the wizard.
    // Drawn by the router, as the app draws it, so its leave guard is live.
    const view = mount(defineComponent({ components: { RouterView }, template: '<RouterView />' }), {
        attachTo: document.body,
        global: { plugins: [pinia, router, [VueQueryPlugin, { queryClient }]] },
    });

    mounted.push(view);

    return view;
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

/** Past the Look step without choosing anything, which is all it asks. */
async function skipLook(view: View): Promise<void> {
    expect(view.get('[data-testid="create-step-look"]').attributes('aria-current')).toBe('step');

    await next(view);
}

async function chooseBanner(view: View, file: File): Promise<void> {
    const input = view.get<HTMLInputElement>('[data-testid="event-banner"]');

    Object.defineProperty(input.element, 'files', { value: [file], configurable: true });
    await input.trigger('change');
    await flushPromises();
}

const HALL = new File(['bytes'], 'hall.jpg', { type: 'image/jpeg' });

let images: ReturnType<typeof fakeImages>;

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

    // jsdom neither decodes images nor makes object URLs, and the wizard
    // measures a Banner and previews it through one.
    images = fakeImages();

    await router.push('/events/new');
    await router.isReady();
});

afterEach(() => {
    mounted.splice(0).forEach((view) => view.unmount());
    document.body.innerHTML = '';
    vi.restoreAllMocks();
    vi.unstubAllGlobals();
});

describe('creating an event', () => {
    it('steps through details, look, format and confirm, then lands the organiser on the event they now lead', async () => {
        const fetch = stubApi({
            '/api/game-systems': GAME_SYSTEMS,
            '/api/countries': COUNTRIES,
            'POST /api/events': { status: 201, body: createdBody() },
        });

        const view = mountView();
        await flushPromises();

        await fillDetails(view);
        await next(view);
        await skipLook(view);

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
        await skipLook(view);
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
        await skipLook(view);

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
        await skipLook(view);
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
        await skipLook(view);
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
        await skipLook(view);
        await view.get('[data-testid="event-game-system"]').setValue('4');
        await next(view);
        await view.get('[data-testid="create-submit"]').trigger('click');
        await flushPromises();

        expect(view.get('[data-testid="create-problem"]').text()).toBe('The status field is prohibited.');
    });
});

describe('the look step', () => {
    /** Details filled, the Banner chosen on Look, and on to Confirm. */
    async function toConfirmWith(view: View, banner: File | null): Promise<void> {
        await fillDetails(view);
        await next(view);

        if (banner !== null) {
            await chooseBanner(view, banner);
        }

        await next(view);
        await view.get('[data-testid="event-game-system"]').setValue('4');
        await next(view);
    }

    function uploadsOf(fetch: ReturnType<typeof stubApi>) {
        return fetch.mock.calls.filter(([url]) => String(url).endsWith('/banner'));
    }

    it('comes second, between details and format', () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();

        expect(view.findAll('[data-testid^="create-step-"]').map((item) => item.text()))
            .toEqual(['Details', 'Look', 'Format', 'Confirm']);
    });

    it('can be skipped entirely, and nothing is uploaded for an event without a banner', async () => {
        const fetch = stubApi({
            '/api/game-systems': GAME_SYSTEMS,
            '/api/countries': COUNTRIES,
            'POST /api/events': { status: 201, body: createdBody() },
        });

        const view = mountView();
        await flushPromises();

        await fillDetails(view);
        await next(view);

        expect(view.get('[data-testid="create-next"]').text()).toBe('Skip');

        await skipLook(view);
        await view.get('[data-testid="event-game-system"]').setValue('4');
        await next(view);
        await view.get('[data-testid="create-submit"]').trigger('click');
        await flushPromises();

        expect(uploadsOf(fetch)).toEqual([]);
        await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('organise'));
        expect(useToastStore().message).toBe('Event created');
    });

    it('previews a chosen banner and holds it, sending nothing until the event exists', async () => {
        const fetch = stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        await fillDetails(view);
        await next(view);
        await chooseBanner(view, HALL);

        expect(view.get<HTMLImageElement>('[data-testid="event-banner-preview"]').element.src).toMatch(/^blob:image-/);
        expect(view.get('[data-testid="create-next"]').text()).toBe('Next');
        expect(fetch.mock.calls.filter(([, init]) => (init as RequestInit | undefined)?.method !== undefined
            && (init as RequestInit).method !== 'GET')).toEqual([]);
    });

    it('refuses a file the api would refuse, and never holds it', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        await fillDetails(view);
        await next(view);
        await chooseBanner(view, new File(['<svg/>'], 'logo.svg', { type: 'image/svg+xml' }));

        expect(view.get('[data-testid="event-banner-error"]').text()).toContain('JPEG, PNG or WebP');
        expect(view.find('[data-testid="event-banner-preview"]').exists()).toBe(false);
        expect(view.get('[data-testid="create-next"]').text()).toBe('Skip');
    });

    it('refuses an image too small for the header before the event exists, rather than after', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });
        images.measure(512, 512);

        const view = mountView();
        await flushPromises();

        await fillDetails(view);
        await next(view);
        await chooseBanner(view, new File(['bytes'], 'logo.png', { type: 'image/png' }));

        expect(view.get('[data-testid="event-banner-error"]').text()).toBe('The banner must be at least 1200 by 400 pixels.');
        expect(view.find('[data-testid="event-banner-preview"]').exists()).toBe(false);
        expect(view.get('[data-testid="create-next"]').text()).toBe('Skip');
    });

    it('lets a held banner go again before anything is sent', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        await fillDetails(view);
        await next(view);
        await chooseBanner(view, HALL);

        const preview = view.get<HTMLImageElement>('[data-testid="event-banner-preview"]').element.src;

        await view.get('[data-testid="event-banner-remove"]').trigger('click');

        expect(view.find('[data-testid="event-banner-preview"]').exists()).toBe(false);
        expect(URL.revokeObjectURL).toHaveBeenCalledWith(preview);
    });

    it('uploads the held banner once the event exists, and says the event was created', async () => {
        const bannered = createdBody({
            banner: { large: 'https://cdn.test/l.webp', small: 'https://cdn.test/s.webp' },
            attendees_count: undefined,
        });
        const fetch = stubApi({
            '/api/game-systems': GAME_SYSTEMS,
            '/api/countries': COUNTRIES,
            'POST /api/events': { status: 201, body: createdBody() },
            'POST /api/events/croydon-doubles/banner': { status: 200, body: bannered },
        });

        const view = mountView();
        await flushPromises();

        await toConfirmWith(view, HALL);
        await view.get('[data-testid="create-submit"]').trigger('click');
        await flushPromises();

        const uploads = uploadsOf(fetch);
        const body = (uploads[0]?.[1] as RequestInit | undefined)?.body;

        expect(uploads).toHaveLength(1);
        expect(body).toBeInstanceOf(FormData);
        expect((body as FormData).get('banner')).toBe(HALL);

        await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('organise'));
        expect(useToastStore().message).toBe('Event created');
        const cached = queryClient.getQueryData<{ banner: unknown; attendees_count?: number }>(['events', 'croydon-doubles']);

        expect(cached?.banner).toEqual({ large: 'https://cdn.test/l.webp', small: 'https://cdn.test/s.webp' });
        // The banner route does not count Attendees; the create's count is kept.
        expect(cached?.attendees_count).toBe(0);
    });

    it('keeps the event when the banner fails to upload, and says where to add it', async () => {
        stubApi({
            '/api/game-systems': GAME_SYSTEMS,
            '/api/countries': COUNTRIES,
            'POST /api/events': { status: 201, body: createdBody() },
            'POST /api/events/croydon-doubles/banner': { status: 500, body: { message: 'Server Error' } },
        });

        const view = mountView();
        await flushPromises();

        await toConfirmWith(view, HALL);
        await view.get('[data-testid="create-submit"]').trigger('click');
        await flushPromises();

        await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('organise'));
        expect(router.currentRoute.value.params.eventSlug).toBe('croydon-doubles');
        expect(useToastStore().message).toBe('Event created, but the banner didn\'t upload. Add it in Settings.');
    });

    it('says why the api refused the banner, when it did', async () => {
        stubApi({
            '/api/game-systems': GAME_SYSTEMS,
            '/api/countries': COUNTRIES,
            'POST /api/events': { status: 201, body: createdBody() },
            'POST /api/events/croydon-doubles/banner': {
                status: 422,
                body: { message: 'Invalid.', errors: { banner: ['The banner failed to upload.'] } },
            },
        });

        const view = mountView();
        await flushPromises();

        await toConfirmWith(view, HALL);
        await view.get('[data-testid="create-submit"]').trigger('click');
        await flushPromises();

        await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('organise'));
        expect(useToastStore().message)
            .toBe('Event created, but the banner didn\'t upload. The banner failed to upload. Add it in Settings.');
    });

    it('sums up the look on the confirm step, with a way back to change it', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        await toConfirmWith(view, HALL);

        expect(view.get('[data-testid="create-summary-look"]').text()).toContain('hall.jpg');

        // In step order: the Look sits between where the event is and how it is played.
        const labels = view.findAll('[data-testid="create-summary"] dt').map((label) => label.text());

        expect(labels.indexOf('Banner')).toBe(labels.indexOf('Where') + 1);
        expect(labels.indexOf('Game system')).toBe(labels.indexOf('Banner') + 1);

        await view.get('[data-testid="create-edit-look"]').trigger('click');
        await flushPromises();

        expect(view.get('[data-testid="create-step-look"]').attributes('aria-current')).toBe('step');
        expect(view.get<HTMLImageElement>('[data-testid="event-banner-preview"]').element.src).toMatch(/^blob:image-/);
    });

    it('says when the event will have no banner', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        await toConfirmWith(view, null);

        expect(view.get('[data-testid="create-summary-look"]').text()).toContain('No banner');
    });
});

describe('moving around the wizard', () => {
    function stepButton(view: View, step: string) {
        return view.find(`[data-testid="create-step-${step}"] button`);
    }

    it('lets the organiser jump back to a step already done, but not on to one not yet reached', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        expect(stepButton(view, 'details').exists()).toBe(false);
        expect(stepButton(view, 'look').exists()).toBe(false);

        await fillDetails(view);
        await view.get('[data-testid="event-venue-city"]').setValue('Croydon');
        await next(view);
        await skipLook(view);

        expect(stepButton(view, 'details').exists()).toBe(true);
        expect(stepButton(view, 'look').exists()).toBe(true);
        expect(stepButton(view, 'confirm').exists()).toBe(false);

        await stepButton(view, 'details').trigger('click');
        await flushPromises();

        expect(view.get('[data-testid="create-step-details"]').attributes('aria-current')).toBe('step');
        expect(view.get<HTMLInputElement>('[data-testid="event-name"]').element.value).toBe('Croydon Doubles');
        expect(view.get<HTMLInputElement>('[data-testid="event-venue-city"]').element.value).toBe('Croydon');
    });

    it('holds the steps in between to what they ask for when jumping forward again', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        await fillDetails(view);
        await next(view);
        await skipLook(view);
        await view.get('[data-testid="event-game-system"]').setValue('4');
        await next(view);

        await stepButton(view, 'details').trigger('click');
        await flushPromises();
        await view.get('[data-testid="event-name"]').setValue('');
        await stepButton(view, 'confirm').trigger('click');
        await flushPromises();

        expect(view.get('[data-testid="create-step-details"]').attributes('aria-current')).toBe('step');
        expect(view.get('[data-testid="event-name-error"]').text()).toContain('name');
    });

    it.each([
        ['details', 'Croydon Doubles'],
        ['look', 'No banner'],
        ['format', 'Horus Heresy'],
    ])('sums up the %s step on confirm, with an edit link back to it', async (step, shown) => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        await fillDetails(view);
        await next(view);
        await skipLook(view);
        await view.get('[data-testid="event-game-system"]').setValue('4');
        await next(view);

        expect(view.get(`[data-testid="create-summary-${step}"]`).text()).toContain(shown);

        await view.get(`[data-testid="create-edit-${step}"]`).trigger('click');
        await flushPromises();

        expect(view.get(`[data-testid="create-step-${step}"]`).attributes('aria-current')).toBe('step');
    });

    it('says on confirm that the event saves as a draft only its organisers can see', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        await fillDetails(view);
        await next(view);
        await skipLook(view);
        await view.get('[data-testid="event-game-system"]').setValue('4');
        await next(view);

        expect(view.get('[data-testid="create-draft-note"]').text()).toMatch(/draft.*organisers.*published/i);
    });
});

describe('leaving the wizard', () => {
    const ELSEWHERE = { name: 'login' } as const;

    function leaveDialog(view: View) {
        return view.get('[data-testid="create-leave"]');
    }

    it('asks before leaving with changes, and stays put when the organiser cancels', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="event-name"]').setValue('Croydon Doubles');
        await router.push(ELSEWHERE);
        await flushPromises();

        expect(router.currentRoute.value.name).toBe('create-event');
        expect(leaveDialog(view).attributes('open')).toBeDefined();

        await view.get('[data-testid="create-leave-cancel"]').trigger('click');
        await flushPromises();

        expect(leaveDialog(view).attributes('open')).toBeUndefined();
        expect(router.currentRoute.value.name).toBe('create-event');
        expect(view.get<HTMLInputElement>('[data-testid="event-name"]').element.value).toBe('Croydon Doubles');
    });

    it('leaves where the organiser was going once they confirm', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="event-name"]').setValue('Croydon Doubles');
        await router.push(ELSEWHERE);
        await flushPromises();
        await view.get('[data-testid="create-leave-confirm"]').trigger('click');

        await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('login'));
    });

    it('goes back, rather than forward again, when the leave was the back button', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });
        await router.push({ name: 'login' });
        await router.push({ name: 'create-event' });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="event-name"]').setValue('Croydon Doubles');
        router.back();
        await vi.waitFor(() => expect(leaveDialog(view).attributes('open')).toBeDefined());
        await view.get('[data-testid="create-leave-confirm"]').trigger('click');

        await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('login'));
        // Gone back: the wizard is ahead in history rather than pushed behind,
        // so Back from here cannot reopen it empty.
        expect(window.history.state.forward).toBe('/events/new');
    });

    it('lets a logout through without asking, since nothing can be saved once signed out', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="event-name"]').setValue('Croydon Doubles');

        // As useLogout does it: the token is forgotten before the move to login.
        await useApiClient().logout();
        await router.replace({ name: 'login' });

        await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('login'));
        expect(view.find('[data-testid="create-leave"][open]').exists()).toBe(false);
    });

    it('lets a lost session send the organiser to sign in without asking', async () => {
        stubApi({
            '/api/game-systems': GAME_SYSTEMS,
            '/api/countries': COUNTRIES,
            'POST /api/events': { status: 401, body: { message: 'Unauthenticated.' } },
        });

        const view = mountView();
        await flushPromises();

        await fillDetails(view);
        await next(view);
        await skipLook(view);
        await view.get('[data-testid="event-game-system"]').setValue('4');
        await next(view);
        await view.get('[data-testid="create-submit"]').trigger('click');
        await flushPromises();

        await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('login'));
        expect(view.find('[data-testid="create-leave"][open]').exists()).toBe(false);
    });

    it('leaves without asking when nothing has been entered', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        await router.push(ELSEWHERE);

        await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('login'));
        expect(view.find('[data-testid="create-leave"][open]').exists()).toBe(false);
    });
});

describe('closing the tab on the wizard', () => {
    /** Whether the browser would be told to ask before closing or reloading. */
    function wouldAsk(): boolean {
        const event = new Event('beforeunload', { cancelable: true });

        window.dispatchEvent(event);

        return event.defaultPrevented;
    }

    it('asks only once something has been entered', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        expect(wouldAsk()).toBe(false);

        await view.get('[data-testid="event-name"]').setValue('Croydon Doubles');

        expect(wouldAsk()).toBe(true);
    });

    it('stops asking once the event is created', async () => {
        stubApi({
            '/api/game-systems': GAME_SYSTEMS,
            '/api/countries': COUNTRIES,
            'POST /api/events': { status: 201, body: createdBody() },
        });

        const view = mountView();
        await flushPromises();

        await fillDetails(view);
        await next(view);
        await skipLook(view);
        await view.get('[data-testid="event-game-system"]').setValue('4');
        await next(view);
        await view.get('[data-testid="create-submit"]').trigger('click');
        await flushPromises();

        await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('organise'));
        expect(wouldAsk()).toBe(false);
    });

    it('stops asking once the organiser has chosen to leave', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="event-name"]').setValue('Croydon Doubles');
        await router.push({ name: 'login' });
        await flushPromises();
        await view.get('[data-testid="create-leave-confirm"]').trigger('click');

        await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('login'));
        expect(wouldAsk()).toBe(false);
    });
});

describe('the url', () => {
    function slugOf(view: View): string {
        return view.get<HTMLInputElement>('[data-testid="create-slug"]').element.value;
    }

    it('fills in from the name until the organiser changes it themselves', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="event-name"]').setValue('Croydon Doubles: Café Edition!');

        expect(slugOf(view)).toBe('croydon-doubles-cafe-edition');

        await view.get('[data-testid="create-slug"]').setValue('croydon-27');
        await view.get('[data-testid="event-name"]').setValue('Croydon Doubles 2027');

        expect(slugOf(view)).toBe('croydon-27');
    });

    it('previews the full address the event will be at', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="event-name"]').setValue('Croydon Doubles');

        expect(view.get('[data-testid="create-slug-hint"]').text())
            .toContain(`${window.location.origin}/events/croydon-doubles`);
    });

    it('is shown in full on confirm, with a warning that it is for good', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();

        await fillDetails(view);
        await next(view);
        await skipLook(view);
        await view.get('[data-testid="event-game-system"]').setValue('4');
        await next(view);

        const url = view.get('[data-testid="create-summary-url"]').text();

        expect(url).toContain(`${window.location.origin}/events/croydon-doubles`);
        expect(url).toContain('Can\'t be changed later');
    });

    describe('availability', () => {
        function checksOf(fetch: ReturnType<typeof stubApi>): string[] {
            return fetch.mock.calls
                .map(([url]) => String(url))
                .filter((url) => url.includes('/api/event-slug-availability'))
                .map((url) => new URL(url).searchParams.get('slug') ?? '');
        }

        beforeEach(() => {
            vi.useFakeTimers({ toFake: ['setTimeout', 'clearTimeout'] });
        });

        afterEach(() => {
            vi.useRealTimers();
        });

        it('waits for the organiser to stop typing, then asks once whether the url is free', async () => {
            const fetch = stubApi({
                '/api/game-systems': GAME_SYSTEMS,
                '/api/countries': COUNTRIES,
                '/api/event-slug-availability': { status: 200, body: { data: { slug: 'croydon-doubles', available: true } } },
            });

            const view = mountView();
            await flushPromises();

            await view.get('[data-testid="create-slug"]').setValue('croydon');
            await vi.advanceTimersByTimeAsync(100);
            await view.get('[data-testid="create-slug"]').setValue('croydon-doubles');
            await vi.advanceTimersByTimeAsync(100);

            expect(checksOf(fetch)).toEqual([]);

            await vi.advanceTimersByTimeAsync(1000);
            await flushPromises();

            expect(checksOf(fetch)).toEqual(['croydon-doubles']);
            expect(view.get('[data-testid="create-slug-hint"]').text()).toContain('available');
        });

        it('says straight away when the url is taken, and will not move on until it is changed', async () => {
            stubApi({
                '/api/game-systems': GAME_SYSTEMS,
                '/api/countries': COUNTRIES,
                '/api/event-slug-availability': { status: 200, body: { data: { slug: 'croydon-doubles', available: false } } },
            });

            const view = mountView();
            await flushPromises();

            await fillDetails(view);
            await vi.advanceTimersByTimeAsync(1000);
            await flushPromises();

            expect(view.get('[data-testid="create-slug-error"]').text()).toBe('That URL is already taken.');

            await next(view);

            expect(view.get('[data-testid="create-step-details"]').attributes('aria-current')).toBe('step');
            expect(view.get('[data-testid="create-slug-error"]').text()).toBe('That URL is already taken.');
        });

        it('clears a refusal once the url it was about has changed', async () => {
            stubApi({
                '/api/game-systems': GAME_SYSTEMS,
                '/api/countries': COUNTRIES,
                '/api/event-slug-availability': { status: 200, body: { data: { slug: 'x', available: false } } },
            });

            const view = mountView();
            await flushPromises();

            await view.get('[data-testid="event-name"]').setValue('Croydon Doubles');
            await vi.advanceTimersByTimeAsync(1000);
            await flushPromises();
            await next(view);

            expect(view.get('[data-testid="create-slug-error"]').text()).toBe('That URL is already taken.');

            // Renaming moves an unedited slug, which is no longer the one refused.
            await view.get('[data-testid="event-name"]').setValue('Croydon Singles');

            expect(view.find('[data-testid="create-slug-error"]').exists()).toBe(false);

            await view.get('[data-testid="create-slug"]').setValue('new');
            await next(view);
            await view.get('[data-testid="create-slug"]').setValue('croydon-27');

            expect(view.find('[data-testid="create-slug-error"]').exists()).toBe(false);
        });

        it('does not ask about a url that could never be used', async () => {
            const fetch = stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

            const view = mountView();
            await flushPromises();

            await view.get('[data-testid="create-slug"]').setValue('new');
            await vi.advanceTimersByTimeAsync(1000);
            await flushPromises();

            expect(checksOf(fetch)).toEqual([]);
        });
    });
});

describe('how the event is played', () => {
    async function toFormat(view: View): Promise<void> {
        await fillDetails(view);
        await next(view);
        await skipLook(view);
        await view.get('[data-testid="event-game-system"]').setValue('4');
    }

    it('offers every pairing format, but only swiss can be chosen yet', async () => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();
        await toFormat(view);

        const formats = ['swiss', 'round_robin', 'knockout', 'random']
            .map((format) => view.get<HTMLInputElement>(`[data-testid="event-pairing-format-${format}"]`));

        expect(formats.map((format) => format.element.disabled)).toEqual([false, true, true, true]);
        expect(formats[0]?.element.checked).toBe(true);

        for (const format of ['round_robin', 'knockout', 'random']) {
            expect(view.get(`[data-testid="event-pairing-format-${format}-option"]`).text()).toContain('Coming soon');
        }

        expect(view.get('[data-testid="event-pairing-format-swiss-option"]').text()).not.toContain('Coming soon');
    });

    it('runs open-ended, with nobody picking a side, unless the organiser says otherwise', async () => {
        const fetch = stubApi({
            '/api/game-systems': GAME_SYSTEMS,
            '/api/countries': COUNTRIES,
            'POST /api/events': { status: 201, body: createdBody() },
        });

        const view = mountView();
        await flushPromises();
        await toFormat(view);
        await next(view);

        const format = view.get('[data-testid="create-summary-format"]').text();
        expect(format).toContain('Swiss');
        expect(format).toContain('Open-ended');
        expect(format).toMatch(/Allegiance\s*No/);

        await view.get('[data-testid="create-submit"]').trigger('click');
        await flushPromises();

        expect(bodyOf(fetch, 'POST')).toMatchObject({
            pairing_format: 'swiss',
            settings: { round_count: null, requires_opposed_allegiance: false },
        });
    });

    it('sends the rounds the organiser set, and that players pick a side', async () => {
        const fetch = stubApi({
            '/api/game-systems': GAME_SYSTEMS,
            '/api/countries': COUNTRIES,
            'POST /api/events': { status: 201, body: createdBody() },
        });

        const view = mountView();
        await flushPromises();
        await toFormat(view);
        await view.get('[data-testid="event-round-count"]').setValue('5');
        await view.get('[data-testid="event-allegiance"]').setValue(true);
        await next(view);

        const format = view.get('[data-testid="create-summary-format"]').text();
        expect(format).toContain('5 rounds');
        expect(format).toMatch(/Allegiance\s*Players pick a side/);

        await view.get('[data-testid="create-submit"]').trigger('click');
        await flushPromises();

        expect(bodyOf(fetch, 'POST')).toMatchObject({
            pairing_format: 'swiss',
            settings: { round_count: 5, requires_opposed_allegiance: true },
        });
    });

    it.each(['0', '2.5', '21'])('will not move on with %s rounds, which the api would refuse', async (rounds) => {
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES });

        const view = mountView();
        await flushPromises();
        await toFormat(view);
        await view.get('[data-testid="event-round-count"]').setValue(rounds);
        await next(view);

        expect(view.get('[data-testid="create-step-format"]').attributes('aria-current')).toBe('step');
        expect(view.get('[data-testid="event-round-count-error"]').text()).toContain('from 1 to 20');
    });

    it('sends the organiser back to format when the api refuses the rounds', async () => {
        stubApi({
            '/api/game-systems': GAME_SYSTEMS,
            '/api/countries': COUNTRIES,
            'POST /api/events': {
                status: 422,
                body: {
                    message: 'Invalid.',
                    errors: { 'settings.round_count': ['Use a whole number of rounds, or leave it empty for open-ended.'] },
                },
            },
        });

        const view = mountView();
        await flushPromises();
        await toFormat(view);
        await next(view);
        await view.get('[data-testid="create-submit"]').trigger('click');
        await flushPromises();

        expect(view.get('[data-testid="create-step-format"]').attributes('aria-current')).toBe('step');
        expect(view.get('[data-testid="event-round-count-error"]').text()).toContain('whole number of rounds');
    });
});

describe('the timezone', () => {
    it('starts on the zone the phone is in, and sends the one the organiser picks', async () => {
        const fetch = stubApi({
            '/api/game-systems': GAME_SYSTEMS,
            '/api/countries': COUNTRIES,
            '/api/timezones': TIMEZONES,
            'POST /api/events': { status: 201, body: createdBody() },
        });

        const view = mountView();
        await flushPromises();

        expect(view.get<HTMLSelectElement>('[data-testid="event-timezone"]').element.value).toBe('Europe/London');

        await fillDetails(view);
        await view.get('[data-testid="event-timezone"]').setValue('America/New_York');
        await next(view);
        await skipLook(view);
        await view.get('[data-testid="event-game-system"]').setValue('4');
        await next(view);

        expect(view.get('[data-testid="create-summary-details"]').text()).toContain('America/New_York');

        await view.get('[data-testid="create-submit"]').trigger('click');
        await flushPromises();

        // 6 March 2027 is before New York's clocks go forward, so 09:00 there is UTC-5.
        expect(bodyOf(fetch, 'POST')).toMatchObject({
            timezone: 'America/New_York',
            starts_at: '2027-03-06T09:00:00-05:00',
        });
    });

    it('starts on the current name for a zone the phone reports by an old one', async () => {
        vi.mocked(Intl.DateTimeFormat.prototype.resolvedOptions)
            .mockReturnValue({ timeZone: 'Asia/Calcutta' } as Intl.ResolvedDateTimeFormatOptions);
        stubApi({ '/api/game-systems': GAME_SYSTEMS, '/api/countries': COUNTRIES, '/api/timezones': TIMEZONES });

        const view = mountView();
        await flushPromises();

        expect(view.get<HTMLSelectElement>('[data-testid="event-timezone"]').element.value).toBe('Asia/Kolkata');
    });

    it('puts a refused timezone under the picker on the details step', async () => {
        stubApi({
            '/api/game-systems': GAME_SYSTEMS,
            '/api/countries': COUNTRIES,
            '/api/timezones': TIMEZONES,
            'POST /api/events': {
                status: 422,
                body: { message: 'Invalid.', errors: { timezone: ['The timezone field must be a valid timezone.'] } },
            },
        });

        const view = mountView();
        await flushPromises();
        await fillDetails(view);
        await next(view);
        await skipLook(view);
        await view.get('[data-testid="event-game-system"]').setValue('4');
        await next(view);
        await view.get('[data-testid="create-submit"]').trigger('click');
        await flushPromises();

        expect(view.get('[data-testid="create-step-details"]').attributes('aria-current')).toBe('step');
        expect(view.get('[data-testid="event-timezone-error"]').text()).toContain('valid timezone');
    });
});
