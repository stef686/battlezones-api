import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { createAppRouter } from '@/router';
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

beforeEach(async () => {
    pinia = createPinia();
    setActivePinia(pinia);
    queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });

    router = createAppRouter();

    const storage = new InMemoryTokenStorage();
    storage.write('a-token');
    createApiClient(router, { baseUrl: 'https://api.test', storage });

    await router.push('/login');
    await router.isReady();
});

afterEach(() => {
    vi.unstubAllGlobals();
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
