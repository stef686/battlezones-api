import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { createAppRouter } from '@/router';
import AddAttendeeView from '@/views/AddAttendeeView.vue';

const EVENT_SLUG = 'london-grand-tournament';

function eventBody(permissions: { organise: boolean }) {
    return {
        data: {
            id: 1,
            name: 'London Grand Tournament',
            slug: EVENT_SLUG,
            status: 'published',
            starts_at: '2026-09-12T09:00:00Z',
            ends_at: '2026-09-13T18:00:00Z',
            attendee_size: 2,
            requires_allegiance: true,
            viewer: {
                is_organiser: permissions.organise,
                is_lead_organiser: false,
                is_attendee: false,
                attendee_id: null,
                permissions: { ...permissions, register: false, manage_organisers: false },
            },
        },
    };
}

const ORGANISER = eventBody({ organise: true });

const FACTIONS = { data: [{ id: 3, name: 'Imperial Fists', slug: 'imperial-fists' }] };

const CREATED = {
    data: { id: 31, name: 'Sons of Terra', allegiance: 'loyalist', members: [], checked_in_at: null },
};

/** Answers by path rather than in order, since the screen reads several things at once. */
function stubApi(routes: Record<string, { status: number; body?: unknown }>) {
    const fetch = vi.fn((url: string, init?: RequestInit) => {
        void init;

        const path = String(url).replace('https://api.test', '').split('?')[0] ?? '';
        const match = Object.entries(routes).find(([pattern]) => path.endsWith(pattern));
        const { status, body } = match?.[1] ?? { status: 404, body: { message: 'Not Found.' } };

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

let router: Router;
let pinia: ReturnType<typeof createPinia>;
let queryClient: QueryClient;

function mountView() {
    return mount(AddAttendeeView as never, ({
        props: { eventSlug: EVENT_SLUG },
        global: {
            plugins: [pinia, router, [VueQueryPlugin, { queryClient }]],
        },
    }) as never);
}

beforeEach(async () => {
    window.localStorage.clear();
    pinia = createPinia();
    setActivePinia(pinia);
    queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });

    router = createAppRouter();
    createApiClient(router, { baseUrl: 'https://api.test', storage: new InMemoryTokenStorage() });

    await router.push(`/events/${EVENT_SLUG}`);
    await router.isReady();
});

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('the add attendee screen', () => {
    it('adds a party the organiser is not in, and lands on the new team', async () => {
        const fetch = stubApi({
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}/attendees`]: { status: 201, body: CREATED },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });
        const push = vi.spyOn(router, 'push');

        const view = mountView();
        await flushPromises();

        expect(view.get('h1').text()).toBe('Add attendee');
        expect(view.find('[data-testid="my-email"]').exists()).toBe(false);

        await view.get('[data-testid="party-name"]').setValue('Sons of Terra');
        await view.get('[data-testid="allegiance"]').setValue('loyalist');
        await view.get('[data-testid="player-0-email"]').setValue('ada@example.com');
        await view.get('[data-testid="player-0-name"]').setValue('Ada Lovelace');
        await view.get('[data-testid="player-0-faction"]').setValue('3');
        await view.get('[data-testid="player-0-army-list"]').setValue('Legion Tactical Squad');
        await view.get('[data-testid="player-1-email"]').setValue('grace@example.com');
        await view.get('form').trigger('submit');
        await flushPromises();

        const created = fetch.mock.calls.find(([url, init]) => String(url).endsWith('/attendees') && init?.method === 'POST')!;
        expect(JSON.parse(created[1]?.body as string)).toEqual({
            name: 'Sons of Terra',
            allegiance: 'loyalist',
            players: [
                { name: 'Ada Lovelace', email: 'ada@example.com', faction_id: 3, army_list: 'Legion Tactical Squad' },
                { email: 'grace@example.com' },
            ],
        });
        expect(push).toHaveBeenCalledWith({ name: 'attendee', params: { eventSlug: EVENT_SLUG, attendeeId: '31' } });
    });

    it('asks for no allegiance where the event does not oppose them', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}`]: {
                status: 200,
                body: { data: { ...ORGANISER.data, requires_allegiance: false } },
            },
        });

        const view = mountView();
        await flushPromises();

        expect(view.find('[data-testid="allegiance"]').exists()).toBe(false);
    });

    it('shows the api\'s refusal against the player it is about', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}/attendees`]: {
                status: 422,
                body: {
                    message: 'The given data was invalid.',
                    errors: { 'players.1.email': ['This player has already entered this event.'] },
                },
            },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        const view = mountView();
        await flushPromises();

        await view.get('form').trigger('submit');
        await flushPromises();

        expect(view.get('[data-testid="player-1-email-error"]').text()).toContain('already entered');
    });

    it('says when the factions could not be read, rather than offering none', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 500, body: { message: 'Server Error' } },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="factions-error"]').text()).toContain('factions could not be loaded');
        expect(view.find('form').exists()).toBe(true);
    });

    it('is not there for a reader who does not run the event', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody({ organise: false }) },
        });

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="missing"]').text().toLowerCase()).toContain('not found');
        expect(view.find('form').exists()).toBe(false);
    });
});
