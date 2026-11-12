import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { createAppRouter } from '@/router';
import { useSessionStore } from '@/stores/session';
import OrganisingView from '@/views/OrganisingView.vue';

function organised(overrides: Record<string, unknown> = {}) {
    return {
        id: 1,
        name: 'London Grand Tournament',
        slug: 'london-grand-tournament',
        description: null,
        status: 'published',
        pairing_format: 'swiss',
        timezone: 'Europe/London',
        starts_at: '2026-09-12T08:00:00Z',
        ends_at: '2026-09-13T17:00:00Z',
        max_attendees: 32,
        attendee_size: 1,
        requires_allegiance: false,
        registration_closes_at: null,
        attendees_count: 0,
        is_full: false,
        venue: { name: 'Warhammer World', address: null, city: 'Nottingham', country: 'GB' },
        banner: null,
        colour: null,
        game_system: { id: 1, name: 'Horus Heresy', slug: 'horus-heresy' },
        ...overrides,
    };
}

function stubApi(status: number, body: unknown) {
    const fetch = vi.fn(() => Promise.resolve({
        ok: status >= 200 && status < 300,
        status,
        headers: new Headers(),
        json: () => Promise.resolve(body),
    }));

    vi.stubGlobal('fetch', fetch);

    return fetch;
}

let router: Router;
let pinia: ReturnType<typeof createPinia>;
let queryClient: QueryClient;

async function mountView() {
    const view = mount(OrganisingView, {
        global: { plugins: [pinia, router, [VueQueryPlugin, { queryClient }]] },
    });

    await flushPromises();

    return view;
}

/** Before every fixture's dates, unless a test moves it: they all read as upcoming. */
const NOW = new Date('2026-09-01T12:00:00Z');

beforeEach(async () => {
    vi.useFakeTimers({ toFake: ['Date'] });
    vi.setSystemTime(NOW);
    pinia = createPinia();
    setActivePinia(pinia);
    queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });

    router = createAppRouter();

    const storage = new InMemoryTokenStorage();
    storage.write('a-token');
    createApiClient(router, { baseUrl: 'https://api.test', storage });

    // Signed in and claimed, so the guard lets the list's own route through.
    useSessionStore().viewer = {
        id: 4,
        public_name: 'Ada Lovelace',
        email: 'ada@example.test',
        is_claimed: true,
        email_verified: true,
        unread_notifications_count: 0,
    };

    await router.push('/login');
    await router.isReady();
});

afterEach(() => {
    vi.unstubAllGlobals();
    vi.useRealTimers();
});

describe('the events a viewer organises', () => {
    it('lists every event they run, in the order the API gives, each leading to its organise hub', async () => {
        const fetch = stubApi(200, {
            data: [
                organised(),
                organised({ id: 2, name: 'Croydon Doubles', slug: 'croydon-doubles', status: 'draft' }),
            ],
        });

        const view = await mountView();

        expect(fetch).toHaveBeenCalledWith('https://api.test/api/organised-events', expect.anything());

        const rows = view.findAll('[data-testid="organised-event"]');

        expect(rows.map((row) => row.get('[data-testid="organised-event-name"]').text()))
            .toEqual(['London Grand Tournament', 'Croydon Doubles']);
        expect(rows[1]?.attributes('href')).toBe('/events/croydon-doubles/organise');
    });

    it('says when and where each event is', async () => {
        stubApi(200, { data: [organised()] });

        const row = (await mountView()).get('[data-testid="organised-event"]');

        expect(row.text()).toContain('Nottingham');
        // In the reader's own locale, so only the parts every locale shows.
        const dates = row.get('[data-testid="organised-event-dates"]').text();

        expect(dates).toContain('12');
        expect(dates).toContain('13');
        expect(dates).toContain('2026');
    });

    it('reads the dates on the event\'s own clock, not the phone\'s', async () => {
        // 21:00 UTC on the 11th is 09:00 on the 12th in Auckland.
        stubApi(200, {
            data: [organised({
                timezone: 'Pacific/Auckland',
                starts_at: '2026-09-11T21:00:00Z',
                ends_at: '2026-09-12T05:00:00Z',
            })],
        });

        const dates = (await mountView()).get('[data-testid="organised-event-dates"]').text();

        expect(dates).toContain('12');
        expect(dates).not.toContain('11');
    });

    it('badges a draft and a cancelled event, and nothing else', async () => {
        stubApi(200, {
            data: ['draft', 'published', 'active', 'completed', 'cancelled'].map((status, index) =>
                organised({ id: index + 1, slug: `event-${status}`, status })),
        });

        const badges = (await mountView()).findAll('[data-testid="organised-event"]')
            .map((row) => row.find('[data-testid="organised-event-status"]'))
            .map((badge) => (badge.exists() ? badge.text() : null));

        expect(badges).toEqual(['Draft', null, null, null, 'Cancelled']);
    });

    it('invites a viewer who runs nothing yet to run an event', async () => {
        stubApi(200, { data: [] });

        const view = await mountView();

        expect(view.get('[data-testid="organising-empty"]').text()).toContain('You\'re not organising any events yet');
        expect(view.get('[data-testid="organising-create"]').attributes('href')).toBe('/events/new');
        expect(view.find('[data-testid="organised-event"]').exists()).toBe(false);
    });

    it('says so when the list cannot be read', async () => {
        stubApi(500, { message: 'Server Error' });

        const view = await mountView();

        expect(view.find('[data-testid="organising-problem"]').exists()).toBe(true);
        expect(view.find('[data-testid="organising-empty"]').exists()).toBe(false);
    });
});

/** One event on each side of now, and none under way. */
const UPCOMING_AND_PAST = [
    organised({ id: 1, name: 'Croydon Doubles', slug: 'croydon-doubles', starts_at: '2026-10-03T08:00:00Z', ends_at: '2026-10-03T17:00:00Z' }),
    organised({ id: 2, name: 'Summer Slam', slug: 'summer-slam', status: 'draft', starts_at: '2026-07-04T08:00:00Z', ends_at: '2026-07-04T17:00:00Z' }),
];

function namesIn(view: Awaited<ReturnType<typeof mountView>>, tab: string): string[] {
    return view.get(`[data-testid="organising-panel-${tab}"]`).findAll('[data-testid="organised-event-name"]').map((name) => name.text());
}

describe('the tabs over the events a viewer organises', () => {
    it('opens on the first tab with anything in it, and greys out the empty ones', async () => {
        stubApi(200, { data: UPCOMING_AND_PAST });

        const view = await mountView();

        expect(view.findAll('[role="tab"]').map((tab) => tab.text())).toEqual(['Happening now', 'Upcoming', 'Past']);
        expect(view.get('[data-testid="organising-tab-happening-now"]').attributes('aria-disabled')).toBe('true');
        expect(view.get('[data-testid="organising-tab-upcoming"]').attributes('aria-selected')).toBe('true');
        expect(namesIn(view, 'upcoming')).toEqual(['Croydon Doubles']);
    });

    it('keeps a draft in the tab its dates put it in, still badged', async () => {
        stubApi(200, { data: UPCOMING_AND_PAST });

        const view = await mountView();
        await view.get('[data-testid="organising-tab-past"]').trigger('click');
        await flushPromises();

        expect(namesIn(view, 'past')).toEqual(['Summer Slam']);
        expect(view.get('[data-testid="organising-panel-past"] [data-testid="organised-event-status"]').text()).toBe('Draft');
    });

    it('puts the open tab in the address, so coming back to the list reopens it', async () => {
        stubApi(200, { data: UPCOMING_AND_PAST });

        await router.push('/organising');
        const view = await mountView();

        await view.get('[data-testid="organising-tab-past"]').trigger('click');
        await vi.waitFor(() => expect(router.currentRoute.value.query.tab).toBe('past'));

        await router.push('/events/summer-slam/organise');
        router.back();

        await vi.waitFor(() => expect(router.currentRoute.value.fullPath).toBe('/organising?tab=past'));
    });

    it('opens the tab the address names', async () => {
        stubApi(200, { data: UPCOMING_AND_PAST });

        await router.push('/organising?tab=past');
        const view = await mountView();

        expect(view.get('[data-testid="organising-tab-past"]').attributes('aria-selected')).toBe('true');
    });

    it('falls back to the first tab with anything in it when the address names an empty or unknown tab', async () => {
        stubApi(200, { data: UPCOMING_AND_PAST });

        for (const named of ['happening-now', 'tomorrow']) {
            await router.push(`/organising?tab=${named}`);
            const view = await mountView();

            expect(view.get('[data-testid="organising-tab-upcoming"]').attributes('aria-selected')).toBe('true');

            view.unmount();
        }
    });

    it('shows three greyed tabs above the invitation to run an event when there is nothing at all', async () => {
        stubApi(200, { data: [] });

        const view = await mountView();

        const tabs = view.findAll('[role="tab"]');

        expect(tabs).toHaveLength(3);
        expect(tabs.every((tab) => tab.attributes('aria-disabled') === 'true')).toBe(true);
        expect(view.get('[data-testid="organising-empty"]').text()).toContain('You\'re not organising any events yet');
    });
});
