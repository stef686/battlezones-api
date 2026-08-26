import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import AppShell from '@/components/AppShell.vue';
import { createAppRouter } from '@/router';

const EVENT_SLUG = 'london-grand-tournament';

function eventBody(overrides: Record<string, unknown> = {}) {
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
            game_system: { id: 1, name: 'The Horus Heresy', slug: 'horus-heresy' },
            venue: { name: 'The Hall', address: null, city: null, country: null },
            documents: [],
            viewer: null,
            ...overrides,
        },
    };
}

function stubEvent(body: unknown = eventBody()) {
    vi.stubGlobal(
        'fetch',
        vi.fn(() =>
            Promise.resolve({
                ok: true,
                status: 200,
                headers: new Headers(),
                json: () => Promise.resolve(body),
            }),
        ),
    );
}

let router: Router;
let pinia: ReturnType<typeof createPinia>;
let queryClient: QueryClient;

function mountShell() {
    return mount(AppShell, {
        slots: { default: '<p data-testid="screen">a screen</p>' },
        global: {
            plugins: [pinia, router, [VueQueryPlugin, { queryClient }]],
        },
    });
}

beforeEach(() => {
    window.localStorage.clear();
    pinia = createPinia();
    setActivePinia(pinia);
    queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });

    router = createAppRouter();
    createApiClient(router, { baseUrl: 'https://api.test', storage: new InMemoryTokenStorage() });
});

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('the app shell', () => {
    it('draws the screen it is given', async () => {
        await router.push(`/events/${EVENT_SLUG}`);
        await router.isReady();

        expect(mountShell().get('[data-testid="screen"]').text()).toBe('a screen');
    });

    it('carries the tab bar on the screens that belong to an event', async () => {
        await router.push(`/events/${EVENT_SLUG}/standings`);
        await router.isReady();

        const view = mountShell();

        expect(view.find('[data-testid="tab-bar"]').exists()).toBe(true);
        expect(view.get('[data-testid="tab-standings"]').attributes('href')).toBe(`/events/${EVENT_SLUG}/standings`);
    });

    it('draws an icon beside every tab label', async () => {
        await router.push(`/events/${EVENT_SLUG}`);
        await router.isReady();

        const view = mountShell();

        for (const tab of ['event', 'schedule', 'my-game', 'standings']) {
            const icon = view.get(`[data-testid="tab-${tab}"] svg`);

            expect(icon.attributes('aria-hidden')).toBe('true');
        }
    });

    it('leaves the tab bar off a screen with no event to navigate', async () => {
        await router.push('/login');
        await router.isReady();

        expect(mountShell().find('[data-testid="tab-bar"]').exists()).toBe(false);
    });
});

describe('the event nav', () => {
    it('sits under the header on a screen that belongs to an event', async () => {
        stubEvent();
        await router.push(`/events/${EVENT_SLUG}/standings`);
        await router.isReady();

        const view = mountShell();
        await flushPromises();

        const html = view.html();

        expect(view.find('[data-testid="event-nav"]').exists()).toBe(true);
        expect(html.indexOf('event-header')).toBeLessThan(html.indexOf('event-nav'));
    });

    it('stays off a screen that belongs to no event', async () => {
        stubEvent();
        await router.push('/login');
        await router.isReady();

        const view = mountShell();
        await flushPromises();

        expect(view.find('[data-testid="event-nav"]').exists()).toBe(false);
    });

    it('pins to the top while the header scrolls away behind it', async () => {
        stubEvent();
        await router.push(`/events/${EVENT_SLUG}`);
        await router.isReady();

        const view = mountShell();
        await flushPromises();

        expect(view.get('[data-testid="event-nav"]').classes()).toContain('sticky');
        expect(view.get('[data-testid="event-header"]').classes()).not.toContain('sticky');
    });
});

describe('the event header', () => {
    it('names the event on a screen that belongs to one', async () => {
        stubEvent();
        await router.push(`/events/${EVENT_SLUG}/standings`);
        await router.isReady();

        const view = mountShell();
        await flushPromises();

        expect(view.get('[data-testid="event-header-name"]').text()).toBe('London Grand Tournament');
    });

    it('stays off a screen that belongs to no event', async () => {
        stubEvent();
        await router.push('/login');
        await router.isReady();

        const view = mountShell();
        await flushPromises();

        expect(view.find('[data-testid="event-header"]').exists()).toBe(false);
    });

    it('carries the game system and the dates alongside the name', async () => {
        stubEvent();
        await router.push(`/events/${EVENT_SLUG}`);
        await router.isReady();

        const view = mountShell();
        await flushPromises();

        expect(view.get('[data-testid="event-header-game-system"]').text()).toBe('The Horus Heresy');
        expect(view.get('[data-testid="event-header-dates"]').text()).toContain('Sep');
    });

    it('leaves out a line the event has nothing to fill it with', async () => {
        stubEvent(eventBody({ game_system: null, starts_at: null, ends_at: null }));
        await router.push(`/events/${EVENT_SLUG}`);
        await router.isReady();

        const view = mountShell();
        await flushPromises();

        expect(view.find('[data-testid="event-header-game-system"]').exists()).toBe(false);
        expect(view.find('[data-testid="event-header-dates"]').exists()).toBe(false);
        expect(view.get('[data-testid="event-header-name"]').text()).toBe('London Grand Tournament');
    });

    it('stays out of the way of an event that cannot be read', async () => {
        vi.stubGlobal(
            'fetch',
            vi.fn(() =>
                Promise.resolve({
                    ok: false,
                    status: 404,
                    headers: new Headers(),
                    json: () => Promise.resolve({ message: 'Not Found.' }),
                }),
            ),
        );

        await router.push(`/events/${EVENT_SLUG}`);
        await router.isReady();

        const view = mountShell();
        await flushPromises();

        expect(view.find('[data-testid="event-header"]').exists()).toBe(false);
    });

    it('runs under the status bar, clearing it with the type rather than the background', async () => {
        stubEvent();
        await router.push(`/events/${EVENT_SLUG}`);
        await router.isReady();

        const view = mountShell();
        await flushPromises();

        const inset = 'pt-[env(safe-area-inset-top)]';

        expect(view.get('[data-testid="event-header"]').classes()).toContain(inset);
        expect(view.element.className).not.toContain(inset);
    });

    it('scrolls away with the page rather than pinning', async () => {
        stubEvent();
        await router.push(`/events/${EVENT_SLUG}`);
        await router.isReady();

        const view = mountShell();
        await flushPromises();

        const classes = view.get('[data-testid="event-header"]').classes();

        expect(classes).not.toContain('fixed');
        expect(classes).not.toContain('sticky');
    });

    it('scrims both ends, so the overlay reads over whatever ends up behind it', async () => {
        stubEvent();
        await router.push(`/events/${EVENT_SLUG}`);
        await router.isReady();

        const view = mountShell();
        await flushPromises();

        const header = view.get('[data-testid="event-header"]');

        expect(header.find('.event-header-scrim-top').exists()).toBe(true);
        expect(header.find('.event-header-scrim-bottom').exists()).toBe(true);
    });
});
