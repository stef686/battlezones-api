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

    it('tells a reader who does not run this event that the page is not there', async () => {
        stubApi({ [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody(false) } });

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="missing"]').text().toLowerCase()).toContain('not found');
        expect(view.find('[data-testid="settings-name"]').exists()).toBe(false);
    });
});
