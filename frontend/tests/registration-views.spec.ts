import { VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { createAppRouter } from '@/router';
import { useSessionStore } from '@/stores/session';
import MyTeamView from '@/views/MyTeamView.vue';
import RegisterView from '@/views/RegisterView.vue';

const EVENT_SLUG = 'london-grand-tournament';

const VIEWER = {
    id: 12,
    public_name: 'Ada Lovelace',
    email: 'ada@example.com',
    is_claimed: true,
    email_verified: true,
    unread_notifications_count: 0,
};

function eventBody(overrides: Record<string, unknown> = {}) {
    return {
        data: {
            id: 1,
            name: 'London Grand Tournament',
            slug: EVENT_SLUG,
            description: null,
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
            viewer: {
                is_organiser: false,
                is_lead_organiser: false,
                is_attendee: false,
                attendee_id: null,
                permissions: { organise: false, register: true, manage_organisers: false },
            },
            ...overrides,
        },
    };
}

const FACTIONS = {
    data: [
        { id: 3, name: 'Imperial Fists', slug: 'imperial-fists' },
        { id: 4, name: 'Sons of Horus', slug: 'sons-of-horus' },
    ],
};

const ATTENDEE = {
    data: {
        id: 9,
        name: 'Sons of Terra',
        allegiance: 'loyalist',
        members: [
            { id: 12, name: 'Ada Lovelace', faction: { id: 3, name: 'Imperial Fists' } },
            { id: 13, name: 'Tarik Torgaddon', faction: null },
        ],
        checked_in_at: null,
    },
};

/**
 * Answers by path rather than in order: the screens fire several reads at
 * once, and a queue would make the tests depend on which resolved first.
 */
function stubApi(routes: Record<string, { status: number; body?: unknown }>) {
    const fetch = vi.fn((url: string, init?: RequestInit) => {
        void init;

        const path = String(url).replace('https://api.test', '');
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

function mountView(component: unknown) {
    return mount(component as never, ({
        props: { eventSlug: EVENT_SLUG },
        global: {
            plugins: [
                pinia,
                router,
                [VueQueryPlugin, { queryClientConfig: { defaultOptions: { queries: { retry: false } } } }],
            ],
        },
    }) as never);
}

beforeEach(async () => {
    window.localStorage.clear();
    pinia = createPinia();
    setActivePinia(pinia);

    router = createAppRouter();

    const storage = new InMemoryTokenStorage();
    storage.write('a-token');
    createApiClient(router, { baseUrl: 'https://api.test', storage });

    useSessionStore().viewer = { ...VIEWER };

    await router.push(`/events/${EVENT_SLUG}/register`);
    await router.isReady();
});

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('registering a team', () => {
    it('asks for one Player per place, with the Captain already filled in', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
        });

        const view = mountView(RegisterView);
        await flushPromises();

        expect(view.findAll('[data-testid^="player-"]').length).toBeGreaterThanOrEqual(2);
        expect(view.get('[data-testid="my-email"]').text()).toBe('ada@example.com');

        // The Captain's address is not a field: registering a party is
        // entering it, and the API refuses one its registrant is not in.
        expect(view.find('[data-testid="player-0-email"]').exists()).toBe(false);
        expect(view.find('[data-testid="player-1-email"]').exists()).toBe(true);
    });

    it('sends the party, its allegiance, and a faction for each Player', async () => {
        const fetch = stubApi({
            [`/api/events/${EVENT_SLUG}/attendees`]: { status: 201, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RegisterView);
        await flushPromises();

        await view.get('[data-testid="party-name"]').setValue('Sons of Terra');
        await view.get('[data-testid="allegiance"]').setValue('loyalist');
        await view.get('[data-testid="player-0-faction"]').setValue('3');
        await view.get('[data-testid="player-1-name"]').setValue('Tarik Torgaddon');
        await view.get('[data-testid="player-1-email"]').setValue('tarik@example.com');
        await view.get('[data-testid="player-1-faction"]').setValue('4');
        await view.get('form').trigger('submit');
        await flushPromises();

        const registration = fetch.mock.calls.find(([url]) => String(url).endsWith('/attendees'))!;
        expect(JSON.parse((registration[1] as RequestInit).body as string)).toEqual({
            name: 'Sons of Terra',
            allegiance: 'loyalist',
            players: [
                { email: 'ada@example.com', faction_id: 3 },
                { name: 'Tarik Torgaddon', email: 'tarik@example.com', faction_id: 4 },
            ],
        });

        await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('my-team'));
    });

    it('leaves out an allegiance the Event does not divide the field on', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody({ requires_allegiance: false }) },
        });

        const view = mountView(RegisterView);
        await flushPromises();

        expect(view.find('[data-testid="allegiance"]').exists()).toBe(false);
    });

    it('says the event is full instead of offering a form that cannot succeed', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody({ is_full: true, attendees_count: 32 }) },
        });

        const view = mountView(RegisterView);
        await flushPromises();

        expect(view.get('[data-testid="event-full"]').text()).toContain('full');
        expect(view.get('[data-testid="places-taken"]').text()).toBe('32 of 32 places taken');
        expect(view.find('form').exists()).toBe(false);
    });

    it('says so when entries are not open to this reader', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}`]: {
                status: 200,
                body: eventBody({
                    viewer: {
                        is_organiser: false,
                        is_lead_organiser: false,
                        is_attendee: false,
                        attendee_id: null,
                        permissions: { organise: false, register: false, manage_organisers: false },
                    },
                }),
            },
        });

        const view = mountView(RegisterView);
        await flushPromises();

        expect(view.find('[data-testid="registration-closed"]').exists()).toBe(true);
        expect(view.find('form').exists()).toBe(false);
    });

    it('reports the last place going while the form was open', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees`]: {
                status: 409,
                body: { message: 'London Grand Tournament is full. Ask an organiser whether there is a waiting list.' },
            },
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RegisterView);
        await flushPromises();

        await view.get('[data-testid="player-1-email"]').setValue('tarik@example.com');
        await view.get('form').trigger('submit');
        await flushPromises();

        expect(view.get('[data-testid="register-problem"]').text()).toContain('is full');
        expect(router.currentRoute.value.name).toBe('register');
    });

    it('shows a rejected partner address against that Player\'s field', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees`]: {
                status: 422,
                body: {
                    message: 'The given data was invalid.',
                    errors: { 'players.1.email': ['This player has already entered this event.'] },
                },
            },
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(RegisterView);
        await flushPromises();

        await view.get('[data-testid="player-1-email"]').setValue('tarik@example.com');
        await view.get('form').trigger('submit');
        await flushPromises();

        expect(view.get('[data-testid="player-1-email-error"]').text()).toContain('already entered');
    });

    it('sends a reader who has already entered to their team', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}`]: {
                status: 200,
                body: eventBody({
                    viewer: {
                        is_organiser: false,
                        is_lead_organiser: false,
                        is_attendee: true,
                        attendee_id: 9,
                        permissions: { organise: false, register: false, manage_organisers: false },
                    },
                }),
            },
        });

        mountView(RegisterView);
        await flushPromises();

        await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('my-team'));
    });
});

describe('amending a team', () => {
    const ENTERED = eventBody({
        viewer: {
            is_organiser: false,
            is_lead_organiser: false,
            is_attendee: true,
            attendee_id: 9,
            permissions: { organise: false, register: false, manage_organisers: false },
        },
    });

    beforeEach(async () => {
        await router.push(`/events/${EVENT_SLUG}/my-team`);
    });

    it('shows the team, the partner, and the faction each of them brings', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
        });

        const view = mountView(MyTeamView);
        await flushPromises();

        expect(view.get('[data-testid="team-name"]').text()).toBe('Sons of Terra');

        // The partner's own faction is theirs to record, so it reads as
        // outstanding rather than as something this Player should fill in.
        expect(view.get('[data-testid="team-mate-13"]').text()).toContain('Faction not chosen');

        expect((view.get('[data-testid="my-faction"]').element as HTMLSelectElement).value).toBe('3');
    });

    it('saves the party details and this Player\'s own faction', async () => {
        const fetch = stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}/my-faction`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
        });

        const view = mountView(MyTeamView);
        await flushPromises();

        await view.get('[data-testid="team-name-field"]').setValue('The Ordo Ludi');
        await view.get('[data-testid="my-faction"]').setValue('4');
        await view.get('form').trigger('submit');
        await flushPromises();

        const amend = fetch.mock.calls.find(([url, init]) => String(url).endsWith('/attendees/9') && init?.method === 'PATCH')!;
        expect(JSON.parse(amend[1]?.body as string)).toEqual({
            name: 'The Ordo Ludi',
            allegiance: 'loyalist',
        });

        const faction = fetch.mock.calls.find(([url]) => String(url).endsWith('/my-faction'))!;
        expect(JSON.parse(faction[1]?.body as string)).toEqual({ faction_id: 4 });

        expect(view.find('[data-testid="team-saved"]').exists()).toBe(true);
    });

    it('reports an allegiance frozen by a live round', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
        });

        const view = mountView(MyTeamView);
        await flushPromises();

        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: {
                status: 422,
                body: {
                    message: 'The given data was invalid.',
                    errors: { allegiance: ['Allegiance cannot change once a round is live.'] },
                },
            },
        });

        await view.get('[data-testid="team-allegiance"]').setValue('traitor');
        await view.get('form').trigger('submit');
        await flushPromises();

        expect(view.get('[data-testid="team-allegiance-error"]').text()).toContain('once a round is live');
    });

    it('sends a reader who has not entered to the entry form', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        mountView(MyTeamView);
        await flushPromises();

        await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('register'));
    });
});
