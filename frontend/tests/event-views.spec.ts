import { VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { formatDay, wallClockTime } from '@/lib/dates';
import { createAppRouter } from '@/router';
import AttendeesView from '@/views/AttendeesView.vue';
import AttendeeView from '@/views/AttendeeView.vue';
import EventView from '@/views/EventView.vue';
import ScheduleView from '@/views/ScheduleView.vue';

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
            venue: { name: 'The Hall', address: '1 Example Street', city: 'London', country: 'GB' },
            documents: [{ id: 5, name: 'Player pack', url: 'https://example.test/pack.pdf', created_at: null }],
            viewer: null,
            ...overrides,
        },
    };
}

const SCHEDULE = {
    data: [
        {
            date: '2026-09-12',
            blocks: [
                {
                    id: 1,
                    label: 'Registration',
                    type: 'other',
                    starts_at: '2026-09-12T08:30:00+01:00',
                    ends_at: '2026-09-12T09:15:00+01:00',
                    display_order: 0,
                    target_id: null,
                    is_target_live: false,
                    round: null,
                },
                {
                    id: 2,
                    label: 'Round 1',
                    type: 'round',
                    starts_at: '2026-09-12T09:30:00+01:00',
                    ends_at: '2026-09-12T12:00:00+01:00',
                    display_order: 1,
                    target_id: 4,
                    is_target_live: true,
                    round: { id: 4, number: 1, name: 'Round 1' },
                },
            ],
        },
    ],
};

const ATTENDEES = {
    data: [
        {
            id: 9,
            name: 'Sons of Terra',
            allegiance: 'loyalist',
            members: [{ id: 12, name: 'Ada Lovelace', faction: { id: 3, name: 'Imperial Fists' } }],
        },
        {
            id: 10,
            name: 'The Warmaster\'s Own',
            allegiance: 'traitor',
            members: [{ id: 13, name: 'Tarik Torgaddon', faction: null }],
        },
    ],
    meta: { current_page: 1, last_page: 2, total: 24 },
};

const ATTENDEE = {
    data: {
        id: 9,
        name: 'Sons of Terra',
        allegiance: 'loyalist',
        members: [
            { id: 12, name: 'Ada Lovelace', faction: { id: 3, name: 'Imperial Fists' } },
            { id: 13, name: 'Grace Hopper', faction: null },
        ],
        checked_in_at: null,
    },
};

const NOT_FOUND = { status: 404, body: { message: 'Not Found.' } };

function stubApi(routes: Record<string, { status: number; body?: unknown }>) {
    const fetch = vi.fn((url: string, init?: RequestInit) => {
        void init;

        const path = String(url).replace('https://api.test', '').split('?')[0] ?? '';
        const match = Object.entries(routes).find(([pattern]) => path.endsWith(pattern));
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

let router: Router;
let pinia: ReturnType<typeof createPinia>;

function mountView(component: unknown, props: Record<string, unknown> = { eventSlug: EVENT_SLUG }) {
    return mount(component as never, ({
        props,
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
    createApiClient(router, { baseUrl: 'https://api.test', storage: new InMemoryTokenStorage() });

    await router.push(`/events/${EVENT_SLUG}`);
    await router.isReady();
});

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('the event page', () => {
    it('shows the details, the venue and the documents', async () => {
        stubApi({ [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() } });

        const view = mountView(EventView);
        await flushPromises();

        expect(view.get('[data-testid="event-name"]').text()).toBe('London Grand Tournament');
        expect(view.get('[data-testid="event-description"]').text()).toContain('doubles event');
        expect(view.get('[data-testid="event-dates"]').text()).not.toBe('');
        expect(view.get('[data-testid="event-places"]').text()).toBe('18 of 32 places taken');

        expect(view.findAll('[data-testid="venue-line"]').map((line) => line.text()))
            .toEqual(['The Hall', '1 Example Street', 'London', 'GB']);

        const document = view.get('[data-testid="document-5"]');
        expect(document.attributes('href')).toBe('https://example.test/pack.pdf');
        // Opened in a new tab, and never handed the opener.
        expect(document.attributes('rel')).toContain('noopener');
    });

    it('treats an event nobody may see exactly as one that does not exist', async () => {
        stubApi({});

        const view = mountView(EventView);
        await flushPromises();

        const notice = view.get('[data-testid="missing"]').text().toLowerCase();

        expect(notice).toContain('not found');
        expect(notice).not.toContain('private');
        expect(notice).not.toContain('hidden');
        expect(notice).not.toContain('permission');
        expect(notice).not.toContain('published');
    });

    it('offers organiser controls only where the viewer context grants them', async () => {
        stubApi({ [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() } });

        const anonymous = mountView(EventView);
        await flushPromises();

        expect(anonymous.find('[data-testid="organiser-controls"]').exists()).toBe(false);

        stubApi({
            [`/api/events/${EVENT_SLUG}`]: {
                status: 200,
                body: eventBody({
                    viewer: {
                        is_organiser: true,
                        is_lead_organiser: true,
                        is_attendee: false,
                        attendee_id: null,
                        permissions: { organise: true, register: true, manage_organisers: true },
                    },
                }),
            },
        });

        pinia = createPinia();
        setActivePinia(pinia);
        const organiser = mountView(EventView);
        await flushPromises();

        expect(organiser.find('[data-testid="organiser-controls"]').exists()).toBe(true);
    });

    it('offers entry to a reader who may enter, and their team to one who has', async () => {
        stubApi({
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

        const view = mountView(EventView);
        await flushPromises();

        expect(view.find('[data-testid="my-team-link"]').exists()).toBe(true);
        expect(view.find('[data-testid="register-link"]').exists()).toBe(false);
    });
});

describe('the schedule', () => {
    it('reads the time the hall reads, not the one the reader is standing in', () => {
        // 09:30 at the Event, whatever timezone the phone is set to.
        expect(wallClockTime('2026-09-12T09:30:00+01:00')).toBe('09:30');
        expect(wallClockTime('2026-09-12T09:30:00-05:00')).toBe('09:30');
    });

    it('names the day the API grouped by, not the day before it', () => {
        // A bare date read as UTC midnight shows the day before to anyone west
        // of Greenwich, which is the whole hazard here.
        expect(formatDay('2026-09-12')).toContain('12');
        expect(formatDay('2026-09-12')).toContain('September');
    });

    it('renders each day in order, marking what is live', async () => {
        stubApi({ [`/api/events/${EVENT_SLUG}/schedule`]: { status: 200, body: SCHEDULE } });

        const view = mountView(ScheduleView);
        await flushPromises();

        const times = view.findAll('[data-testid="block-time"]').map((node) => node.text());
        expect(times).toEqual(['08:30', '09:30']);

        expect(view.get('[data-testid="block-2"]').find('[data-testid="block-live"]').exists()).toBe(true);
        expect(view.get('[data-testid="block-1"]').find('[data-testid="block-live"]').exists()).toBe(false);
    });

    it('says an empty schedule is empty rather than showing nothing at all', async () => {
        stubApi({ [`/api/events/${EVENT_SLUG}/schedule`]: { status: 200, body: { data: [] } } });

        const view = mountView(ScheduleView);
        await flushPromises();

        expect(view.find('[data-testid="schedule-empty"]').exists()).toBe(true);
    });

    it('gives a schedule for a missing event the same answer as the event page', async () => {
        stubApi({});

        const view = mountView(ScheduleView);
        await flushPromises();

        expect(view.get('[data-testid="missing"]').text().toLowerCase()).not.toContain('private');
    });
});

describe('the attendee list', () => {
    it('shows each team with its allegiance and its players', async () => {
        stubApi({ [`/api/events/${EVENT_SLUG}/attendees`]: { status: 200, body: ATTENDEES } });

        const view = mountView(AttendeesView);
        await flushPromises();

        expect(view.get('[data-testid="attendee-total"]').text()).toBe('24 teams');

        const loyalist = view.get('[data-testid="attendee-9"]');
        expect(loyalist.text()).toContain('Sons of Terra');
        expect(loyalist.text()).toContain('Ada Lovelace');
        expect(loyalist.find('[data-testid="allegiance-loyalist"]').exists()).toBe(true);

        expect(view.get('[data-testid="attendee-10"]').find('[data-testid="allegiance-traitor"]').exists()).toBe(true);
    });

    it('does not rely on colour alone to say which side a team is on', async () => {
        stubApi({ [`/api/events/${EVENT_SLUG}/attendees`]: { status: 200, body: ATTENDEES } });

        const view = mountView(AttendeesView);
        await flushPromises();

        expect(view.get('[data-testid="allegiance-loyalist"]').text()).toBe('Loyalist');
        expect(view.get('[data-testid="allegiance-traitor"]').text()).toBe('Traitor');
    });

    it('searches, and starts the results again from the first page', async () => {
        const fetch = stubApi({ [`/api/events/${EVENT_SLUG}/attendees`]: { status: 200, body: ATTENDEES } });

        const view = mountView(AttendeesView);
        await flushPromises();

        await view.get('[data-testid="next-page"]').trigger('click');
        await flushPromises();

        await view.get('[data-testid="attendee-search"]').setValue('horus');
        await flushPromises();

        const last = String(fetch.mock.calls.at(-1)![0]);
        expect(last).toContain('search=horus');
        expect(last).not.toContain('page=');
    });

    it('pages through the list', async () => {
        const fetch = stubApi({ [`/api/events/${EVENT_SLUG}/attendees`]: { status: 200, body: ATTENDEES } });

        const view = mountView(AttendeesView);
        await flushPromises();

        expect(view.get('[data-testid="page-position"]').text()).toBe('Page 1 of 2');

        await view.get('[data-testid="next-page"]').trigger('click');
        await flushPromises();

        expect(String(fetch.mock.calls.at(-1)![0])).toContain('page=2');
    });

    it('says nothing matched rather than looking broken', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees`]: {
                status: 200,
                body: { data: [], meta: { current_page: 1, last_page: 1, total: 0 } },
            },
        });

        const view = mountView(AttendeesView);
        await flushPromises();

        expect(view.find('[data-testid="attendees-empty"]').exists()).toBe(true);
    });
});

describe('a vote that has opened', () => {
    const ENTERED = eventBody({
        viewer: {
            is_organiser: false,
            is_lead_organiser: false,
            is_attendee: true,
            attendee_id: 9,
            permissions: { organise: false, register: false, manage_organisers: false },
        },
    });

    function poll(overrides: Record<string, unknown> = {}) {
        return {
            id: 1,
            name: 'Best Painted Army',
            type: 'painting',
            votes_per_player: 2,
            opens_at: null,
            closes_at: null,
            is_open: false,
            is_open_for_me: false,
            my_ballot: [],
            ...overrides,
        };
    }

    it('tells a Player voting is open on the screen they are already on', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/polls`]: { status: 200, body: { data: [poll({ is_open: true, is_open_for_me: true })] } },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
        });

        const view = mountView(EventView);
        await flushPromises();

        // Nobody should have to go looking for a vote that has a window.
        expect(view.get('[data-testid="voting-open"]').text()).toContain('Best Painted Army');
        expect(view.get('[data-testid="voting-open"]').attributes('href')).toBe(`/events/${EVENT_SLUG}/polls/1`);
    });

    it('does not announce a vote this Player cannot cast yet', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/polls`]: { status: 200, body: { data: [poll({ is_open: true, is_open_for_me: false })] } },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
        });

        const view = mountView(EventView);
        await flushPromises();

        expect(view.find('[data-testid="voting-open"]').exists()).toBe(false);
    });

    it('leaves the sections to the event nav rather than listing them again', async () => {
        stubApi({ [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() } });

        const view = mountView(EventView);
        await flushPromises();

        for (const link of ['schedule-link', 'rounds-link', 'attendees-link', 'polls-link', 'standings-link']) {
            expect(view.find(`[data-testid="${link}"]`).exists()).toBe(false);
        }
    });

    it('puts a player one tap from the table they are playing on', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
            [`/api/events/${EVENT_SLUG}/polls`]: { status: 200, body: { data: [] } },
            [`/api/events/${EVENT_SLUG}/my-game`]: { status: 200, body: { data: { id: 18, table_number: 5, is_bye: false, round: { id: 4, number: 2, name: 'Round 2' }, result: { submitted_at: null, edited_at: null, is_flagged: false }, attendees: [] } } },
        });

        const view = mountView(EventView);
        await flushPromises();

        expect(view.get('[data-testid="my-game-link"]').attributes('href')).toBe(`/events/${EVENT_SLUG}/my-game`);
    });

    it('says nothing about a game that is not being played', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
            [`/api/events/${EVENT_SLUG}/polls`]: { status: 200, body: { data: [] } },
            [`/api/events/${EVENT_SLUG}/my-game`]: { status: 200, body: { data: null } },
        });

        const view = mountView(EventView);
        await flushPromises();

        expect(view.find('[data-testid="my-game-link"]').exists()).toBe(false);
    });

    it('does not go looking for a game on behalf of a reader who has not entered', async () => {
        const fetch = stubApi({ [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() } });

        const view = mountView(EventView);
        await flushPromises();

        expect(view.find('[data-testid="my-game-link"]').exists()).toBe(false);
        expect(fetch.mock.calls.some(([url]) => String(url).includes('/my-game'))).toBe(false);
    });
});

describe('the attendee detail', () => {
    it('carries no back link, because the attendees chip is pinned a tap away', async () => {
        stubApi({ [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: ATTENDEE } });

        const view = mountView(AttendeeView, { eventSlug: EVENT_SLUG, attendeeId: '9' });
        await flushPromises();

        expect(view.find('[data-testid="back-to-attendees"]').exists()).toBe(false);
    });

    it('shows the players and the faction each of them brings', async () => {
        stubApi({ [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: ATTENDEE } });

        const view = mountView(AttendeeView, { eventSlug: EVENT_SLUG, attendeeId: '9' });
        await flushPromises();

        expect(view.get('[data-testid="attendee-name"]').text()).toBe('Sons of Terra');
        expect(view.find('[data-testid="allegiance-loyalist"]').exists()).toBe(true);

        expect(view.get('[data-testid="member-12"]').text()).toContain('Imperial Fists');
        expect(view.get('[data-testid="member-13"]').text()).toContain('Faction not chosen');
    });

    it('shows a revealed army list, so an opponent can prepare against it', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: {
                status: 200,
                body: {
                    data: {
                        ...ATTENDEE.data,
                        members: [
                            { id: 12, name: 'Ada Lovelace', faction: { id: 3, name: 'Imperial Fists' }, army_list_locked: true, army_list: 'Legion Tactical Squad, 10 models' },
                            { id: 13, name: 'Grace Hopper', faction: null, army_list_locked: true, army_list: null },
                        ],
                    },
                },
            },
        });

        const view = mountView(AttendeeView, { eventSlug: EVENT_SLUG, attendeeId: '9' });
        await flushPromises();

        expect(view.get('[data-testid="army-list-12"]').text()).toContain('Legion Tactical Squad');

        // Locked with nothing in it is a Player who submitted an empty list,
        // not a list being withheld.
        expect(view.get('[data-testid="army-list-13"]').text()).toContain('No list');
    });

    it('does not pretend an unrevealed list is missing, it says it is not out yet', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: {
                status: 200,
                body: {
                    data: {
                        ...ATTENDEE.data,
                        members: [
                            { id: 12, name: 'Ada Lovelace', faction: { id: 3, name: 'Imperial Fists' }, army_list_locked: true },
                            { id: 13, name: 'Grace Hopper', faction: null, army_list_locked: false },
                        ],
                    },
                },
            },
        });

        const view = mountView(AttendeeView, { eventSlug: EVENT_SLUG, attendeeId: '9' });
        await flushPromises();

        expect(view.get('[data-testid="lists-not-revealed"]').text()).toContain('not been revealed');
        expect(view.find('[data-testid="army-list-12"]').exists()).toBe(false);

        // Who is still holding the team up is not a secret, and is the only
        // thing anyone can act on while the lists are closed.
        expect(view.get('[data-testid="member-13"]').text()).toContain('List not submitted');
    });

    it('lets an organiser open a held-up team\'s lists and reopen one player\'s', async () => {
        const ORGANISER = eventBody({
            viewer: {
                is_organiser: true,
                is_lead_organiser: true,
                is_attendee: false,
                attendee_id: null,
                permissions: { organise: true, register: false, manage_organisers: true },
            },
        });

        const fetch = stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9/army-lists/reveal`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}/attendees/9/members/12/army-list/unlock`]: { status: 200, body: { data: {} } },
            [`/api/events/${EVENT_SLUG}/attendees/9`]: {
                status: 200,
                body: {
                    data: {
                        ...ATTENDEE.data,
                        // Held up by a partner who never submitted, so nobody
                        // sees the lists — the Organiser included.
                        members: [
                            { id: 12, name: 'Ada Lovelace', faction: { id: 3, name: 'Imperial Fists' }, army_list_locked: true },
                            { id: 13, name: 'Grace Hopper', faction: null, army_list_locked: false },
                        ],
                    },
                },
            },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        const view = mountView(AttendeeView, { eventSlug: EVENT_SLUG, attendeeId: '9' });
        await flushPromises();

        await view.get('[data-testid="reveal-army-lists"]').trigger('click');
        await flushPromises();

        expect(fetch.mock.calls.some(([url]) => String(url).endsWith('/attendees/9/army-lists/reveal'))).toBe(true);

        // Only a locked list has anything to reopen.
        expect(view.find('[data-testid="unlock-13"]').exists()).toBe(false);

        await view.get('[data-testid="unlock-12"]').trigger('click');
        await flushPromises();

        expect(fetch.mock.calls.some(([url]) => String(url).endsWith('/members/12/army-list/unlock'))).toBe(true);
    });

    it('offers no organiser controls to a reader who does not run the event', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        const view = mountView(AttendeeView, { eventSlug: EVENT_SLUG, attendeeId: '9' });
        await flushPromises();

        expect(view.find('[data-testid="reveal-army-lists"]').exists()).toBe(false);
        expect(view.find('[data-testid="unlock-12"]').exists()).toBe(false);
    });

    it('answers a team that is not there the same way as a missing event', async () => {
        stubApi({});

        const view = mountView(AttendeeView, { eventSlug: EVENT_SLUG, attendeeId: '404' });
        await flushPromises();

        const notice = view.get('[data-testid="missing"]').text().toLowerCase();

        expect(notice).toContain('not found');
        expect(notice).not.toContain('private');
    });
});
