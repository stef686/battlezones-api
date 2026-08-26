import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { createAppRouter } from '@/router';
import EventSettingsView from '@/views/EventSettingsView.vue';

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
            game_system: null,
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
    return mount(EventSettingsView as never, ({
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

    await router.push(`/events/${EVENT_SLUG}/organise/settings`);
    await router.isReady();
});

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('the event settings screen', () => {
    it('opens on what the event says it is', async () => {
        stubApi({ [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() } });

        const view = mountView();
        await flushPromises();

        expect(valueOf(view, 'settings-name')).toBe('London Grand Tournament');
        expect(valueOf(view, 'settings-venue-city')).toBe('London');
        expect(valueOf(view, 'settings-max-attendees')).toBe('32');
    });

    it('sends only what the organiser changed', async () => {
        const fetch = stubApi({
            [`GET /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`PATCH /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody(true, { name: 'Croydon Grand Tournament' }) },
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="settings-name"]').setValue('Croydon Grand Tournament');
        await view.get('[data-testid="settings-save"]').trigger('submit');
        await flushPromises();

        const patch = fetch.mock.calls.find(([, init]) => (init as RequestInit | undefined)?.method === 'PATCH');

        expect(patch).toBeDefined();
        expect(JSON.parse(String((patch?.[1] as RequestInit).body))).toEqual({ name: 'Croydon Grand Tournament' });
        expect(view.get('[data-testid="settings-saved"]').text()).toContain('Saved');
    });

    it('puts a rejected field\'s reason under that field', async () => {
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

        await view.get('[data-testid="settings-max-attendees"]').setValue('2');
        await view.get('[data-testid="settings-save"]').trigger('submit');
        await flushPromises();

        expect(view.get('[data-testid="settings-max-attendees-error"]').text())
            .toContain('There are already 18 parties entered.');
    });

    it('uploads a banner as multipart, because a patch body carries no files', async () => {
        const bannered = eventBody(true, { banner: { large: 'https://cdn.test/l.webp', small: 'https://cdn.test/s.webp' } });

        const fetch = stubApi({
            [`GET /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`POST /api/events/${EVENT_SLUG}/banner`]: { status: 200, body: bannered },
        });

        const view = mountView();
        await flushPromises();

        const input = view.get<HTMLInputElement>('[data-testid="settings-banner"]');
        const file = new File(['bytes'], 'hall.jpg', { type: 'image/jpeg' });

        Object.defineProperty(input.element, 'files', { value: [file] });
        await input.trigger('change');
        await flushPromises();

        const upload = fetch.mock.calls.find(([url]) => String(url).endsWith('/banner'));

        expect(upload).toBeDefined();
        expect((upload?.[1] as RequestInit).body).toBeInstanceOf(FormData);
        expect(view.get<HTMLImageElement>('[data-testid="settings-banner-preview"]').element.src)
            .toBe('https://cdn.test/l.webp');
    });

    it('says why a rejected upload was rejected, against the field that carried it', async () => {
        stubApi({
            [`GET /api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`POST /api/events/${EVENT_SLUG}/banner`]: {
                status: 422,
                body: { message: 'Invalid.', errors: { banner: ['The banner must be at least 1200 pixels wide.'] } },
            },
        });

        const view = mountView();
        await flushPromises();

        const input = view.get<HTMLInputElement>('[data-testid="settings-banner"]');
        Object.defineProperty(input.element, 'files', { value: [new File(['bytes'], 'logo.png', { type: 'image/png' })] });
        await input.trigger('change');
        await flushPromises();

        expect(view.get('[data-testid="settings-banner-error"]').text())
            .toContain('at least 1200 pixels wide');
    });

    it('removes a banner, returning the header to its flat state', async () => {
        const bannered = eventBody(true, { banner: { large: 'https://cdn.test/l.webp', small: 'https://cdn.test/s.webp' } });

        const fetch = stubApi({
            [`GET /api/events/${EVENT_SLUG}`]: { status: 200, body: bannered },
            [`DELETE /api/events/${EVENT_SLUG}/banner`]: { status: 200, body: eventBody() },
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="settings-banner-remove"]').trigger('click');
        await flushPromises();

        expect(fetch.mock.calls.some(([, init]) => (init as RequestInit | undefined)?.method === 'DELETE')).toBe(true);
        expect(view.find('[data-testid="settings-banner-preview"]').exists()).toBe(false);
    });

    it('offers nothing to remove when there is no banner', async () => {
        stubApi({ [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() } });

        const view = mountView();
        await flushPromises();

        expect(view.find('[data-testid="settings-banner-remove"]').exists()).toBe(false);
    });

    it('tells a reader who does not run this event that the page is not there', async () => {
        stubApi({ [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody(false) } });

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="missing"]').text().toLowerCase()).toContain('not found');
        expect(view.find('[data-testid="settings-name"]').exists()).toBe(false);
    });
});
