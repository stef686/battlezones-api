import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import EventNav from '@/components/EventNav.vue';
import { createAppRouter } from '@/router';

const EVENT_SLUG = 'london-grand-tournament';

function eventBody(viewer: Record<string, unknown> | null = null) {
    return {
        data: {
            id: 1,
            name: 'London Grand Tournament',
            slug: EVENT_SLUG,
            description: null,
            status: 'active',
            starts_at: '2026-09-12T09:00:00Z',
            ends_at: '2026-09-13T18:00:00Z',
            max_attendees: 32,
            attendee_size: 2,
            requires_allegiance: true,
            registration_closes_at: null,
            attendees_count: 18,
            is_full: false,
            game_system: { id: 1, name: 'The Horus Heresy', slug: 'horus-heresy' },
            venue: { name: null, address: null, city: null, country: null },
            documents: [],
            viewer,
        },
    };
}

function entrant(overrides: Record<string, unknown> = {}) {
    return {
        is_organiser: false,
        is_lead_organiser: false,
        is_attendee: true,
        attendee_id: 7,
        permissions: { organise: false, register: false, manage_organisers: false },
        ...overrides,
    };
}

function stubEvent(body: unknown) {
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

/** jsdom has no layout, so it ships no scrollIntoView to spy on. */
let scrollIntoView: ReturnType<typeof vi.fn>;

let router: Router;
let pinia: ReturnType<typeof createPinia>;
let queryClient: QueryClient;

function mountNav() {
    return mount(EventNav, {
        props: { eventSlug: EVENT_SLUG },
        global: { plugins: [pinia, router, [VueQueryPlugin, { queryClient }]] },
    });
}

beforeEach(() => {
    window.localStorage.clear();
    pinia = createPinia();
    setActivePinia(pinia);
    queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });

    scrollIntoView = vi.fn();
    Element.prototype.scrollIntoView = scrollIntoView as unknown as Element['scrollIntoView'];

    router = createAppRouter();
    createApiClient(router, { baseUrl: 'https://api.test', storage: new InMemoryTokenStorage() });
});

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('the event nav', () => {
    it('lists the sections of an event in the order they matter', async () => {
        stubEvent(eventBody());
        await router.push(`/events/${EVENT_SLUG}`);
        await router.isReady();

        const view = mountNav();
        await flushPromises();

        expect(view.findAll('[data-testid^="event-nav-"]').map((chip) => chip.text()))
            .toEqual(['Home', 'Rounds', 'Standings', 'Attendees', 'Schedule']);
    });

    it('offers my team to a viewer who has entered, last of the chips', async () => {
        stubEvent(eventBody(entrant()));
        await router.push(`/events/${EVENT_SLUG}`);
        await router.isReady();

        const view = mountNav();
        await flushPromises();

        expect(view.findAll('[data-testid^="event-nav-"]').map((chip) => chip.text()))
            .toEqual(['Home', 'Rounds', 'Standings', 'Attendees', 'Schedule', 'My team']);
        expect(view.get('[data-testid="event-nav-my-team"]').attributes('href'))
            .toBe(`/events/${EVENT_SLUG}/my-team`);
    });

    it('leaves my team out for a viewer who has not entered', async () => {
        stubEvent(eventBody(entrant({ is_attendee: false, attendee_id: null })));
        await router.push(`/events/${EVENT_SLUG}`);
        await router.isReady();

        const view = mountNav();
        await flushPromises();

        expect(view.find('[data-testid="event-nav-my-team"]').exists()).toBe(false);
    });

    it('lights home on the event screen itself', async () => {
        stubEvent(eventBody());
        await router.push(`/events/${EVENT_SLUG}`);
        await router.isReady();

        const view = mountNav();
        await flushPromises();

        expect(view.get('[data-testid="event-nav-event"]').attributes('href')).toBe(`/events/${EVENT_SLUG}`);
        expect(view.get('[data-testid="event-nav-event"]').attributes('aria-current')).toBe('page');
    });

    it('lights the section a detail screen belongs to', async () => {
        stubEvent(eventBody());
        await router.push(`/events/${EVENT_SLUG}/rounds/4`);
        await router.isReady();

        const view = mountNav();
        await flushPromises();

        expect(view.get('[data-testid="event-nav-rounds"]').attributes('aria-current')).toBe('page');
        expect(view.get('[data-testid="event-nav-event"]').attributes('aria-current')).toBeUndefined();
        expect(view.get('[data-testid="event-nav-standings"]').attributes('aria-current')).toBeUndefined();
    });

    it('lights attendees from an attendee of that event', async () => {
        stubEvent(eventBody());
        await router.push(`/events/${EVENT_SLUG}/attendees/7`);
        await router.isReady();

        const view = mountNav();
        await flushPromises();

        expect(view.get('[data-testid="event-nav-attendees"]').attributes('aria-current')).toBe('page');
    });

    it('lights nothing on a screen the nav does not reach', async () => {
        stubEvent(eventBody(entrant()));
        await router.push(`/events/${EVENT_SLUG}/my-game`);
        await router.isReady();

        const view = mountNav();
        await flushPromises();

        expect(view.findAll('[data-testid^="event-nav-"]').filter((chip) => chip.attributes('aria-current') !== undefined))
            .toHaveLength(0);
    });

    it('scrolls natively, and fades its trailing edge to say there is more', async () => {
        stubEvent(eventBody());
        await router.push(`/events/${EVENT_SLUG}`);
        await router.isReady();

        const view = mountNav();
        await flushPromises();

        expect(view.get('[data-testid="event-nav"]').classes()).toContain('event-nav-fade');

        const strip = view.get('[data-testid="event-nav"] ul').classes();

        expect(strip).toContain('overflow-x-auto');
        expect(strip.some((name) => name.includes('snap'))).toBe(false);
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

        const view = mountNav();
        await flushPromises();

        expect(view.find('[data-testid="event-nav"]').exists()).toBe(false);
    });

    it('scrolls the lit chip into view, so a deep link does not look like nothing is selected', async () => {
        stubEvent(eventBody());
        await router.push(`/events/${EVENT_SLUG}/schedule`);
        await router.isReady();

        const view = mountNav();
        await flushPromises();

        expect(scrollIntoView).toHaveBeenCalled();
        expect(scrollIntoView.mock.instances.at(-1)).toBe(view.get('[data-testid="event-nav-schedule"]').element);

        await router.push(`/events/${EVENT_SLUG}/standings`);
        await flushPromises();

        expect(scrollIntoView.mock.instances.at(-1)).toBe(view.get('[data-testid="event-nav-standings"]').element);
    });
});
