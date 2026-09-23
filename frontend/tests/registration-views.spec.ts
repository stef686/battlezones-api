import { VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { createAppRouter } from '@/router';
import { useSessionStore } from '@/stores/session';
import MyArmyListView from '@/views/MyArmyListView.vue';
import MyFactionView from '@/views/MyFactionView.vue';
import MyTeamView from '@/views/MyTeamView.vue';
import PaintedArmyView from '@/views/PaintedArmyView.vue';
import PartnerView from '@/views/PartnerView.vue';
import TeamDetailsView from '@/views/TeamDetailsView.vue';
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

describe('the my team hub', () => {
    const ENTERED = eventBody({
        viewer: {
            is_organiser: false,
            is_lead_organiser: false,
            is_attendee: true,
            attendee_id: 9,
            permissions: { organise: false, register: false, manage_organisers: false },
        },
    });

    const PAINTING_POLL = {
        data: [{
            id: 1,
            name: 'Best Painted Army',
            type: 'painting',
            votes_per_player: 2,
            opens_at: null,
            closes_at: null,
            is_open: false,
            is_open_for_me: false,
            my_ballot: [],
        }],
    };

    beforeEach(async () => {
        await router.push(`/events/${EVENT_SLUG}/my-team`);
    });

    it('says where every part of the entry stands, without opening any of them', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: {
                status: 200,
                body: {
                    data: {
                        ...ATTENDEE.data,
                        members: [
                            { ...ATTENDEE.data.members[0], membership_id: 41, invite_outstanding: false },
                            { ...ATTENDEE.data.members[1], membership_id: 42, invite_outstanding: true },
                        ],
                    },
                },
            },
            [`/api/events/${EVENT_SLUG}/polls`]: { status: 200, body: PAINTING_POLL },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
        });

        const view = mountView(MyTeamView);
        await flushPromises();

        // The team as the rest of the Event sees it, above the rows that edit it.
        expect(view.get('[data-testid="team-name"]').text()).toBe('Sons of Terra');
        expect(view.get('[data-testid="team-avatar-placeholder"]').text()).toBe('ST');
        expect(view.find('[data-testid="allegiance-loyalist"]').exists()).toBe(true);

        expect(view.get('[data-testid="team-details-row"]').text()).toContain('Sons of Terra');
        expect(view.get('[data-testid="my-details-row"]').text()).toContain('Imperial Fists');
        expect(view.get('[data-testid="my-list-row"]').text()).toContain('Not submitted');

        // A partner who has not answered is the thing most likely to sink an
        // entry, so the hub says so rather than making it a screen to open.
        expect(view.get('[data-testid="partner-row"]').text()).toContain('waiting');
        expect(view.get('[data-testid="painting-row"]').text()).toContain('Not entered');
    });

    it('offers no partner row where the event is played alone, and no painting row without the vote', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}/polls`]: { status: 200, body: { data: [] } },
            [`/api/events/${EVENT_SLUG}`]: {
                status: 200,
                body: eventBody({ attendee_size: 1, viewer: ENTERED.data.viewer }),
            },
        });

        const view = mountView(MyTeamView);
        await flushPromises();

        expect(view.find('[data-testid="partner-row"]').exists()).toBe(false);
        expect(view.find('[data-testid="painting-row"]').exists()).toBe(false);
    });

    it('sends a reader who has not entered to the entry form', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody() },
        });

        mountView(MyTeamView);
        await flushPromises();

        await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('register'));
    });
});

describe('the my team screens', () => {
    const ENTERED = eventBody({
        viewer: {
            is_organiser: false,
            is_lead_organiser: false,
            is_attendee: true,
            attendee_id: 9,
            permissions: { organise: false, register: false, manage_organisers: false },
        },
    });

    /** The team as its own members read it, which is the only place the seat ids are sent. */
    const MY_TEAM = {
        data: {
            ...ATTENDEE.data,
            members: [
                { id: 12, name: 'Ada Lovelace', faction: { id: 3, name: 'Imperial Fists' }, army_list_locked: false, army_list: null, membership_id: 41, invite_outstanding: false },
                { id: 13, name: 'Tarik Torgadon', faction: null, army_list_locked: false, army_list: null, membership_id: 42, invite_outstanding: true, email: 'tarik@exmaple.com' },
            ],
        },
    };

    beforeEach(async () => {
        await router.push(`/events/${EVENT_SLUG}/my-team/details`);
    });

    it('saves the team name and allegiance', async () => {
        const fetch = stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
        });

        const view = mountView(TeamDetailsView);
        await flushPromises();

        expect((view.get('[data-testid="team-name-field"]').element as HTMLInputElement).value).toBe('Sons of Terra');

        await view.get('[data-testid="team-name-field"]').setValue('The Ordo Ludi');
        await view.get('form').trigger('submit');
        await flushPromises();

        const amend = fetch.mock.calls.find(([url, init]) => String(url).endsWith('/attendees/9') && init?.method === 'PATCH')!;
        expect(JSON.parse(amend[1]?.body as string)).toEqual({ name: 'The Ordo Ludi', allegiance: 'loyalist' });
        expect(view.find('[data-testid="team-saved"]').exists()).toBe(true);
    });

    it('closes the allegiance field once the event has begun, rather than refusing the change later', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: {
                status: 200,
                body: { data: { ...ATTENDEE.data, allegiance_locked: true } },
            },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
        });

        const view = mountView(TeamDetailsView);
        await flushPromises();

        const allegiance = view.get('[data-testid="team-allegiance"]');

        expect((allegiance.element as HTMLSelectElement).disabled).toBe(true);
        expect(view.text()).toContain('Frozen now the event has begun');
    });

    it('uploads a team avatar on its own request, and offers to take it off again', async () => {
        const fetch = stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9/avatar`]: {
                status: 200,
                body: { data: { ...ATTENDEE.data, avatar: 'https://uploads.test/badge.webp' } },
            },
            [`/api/events/${EVENT_SLUG}/attendees/9`]: {
                status: 200,
                body: { data: { ...ATTENDEE.data, avatar: 'https://uploads.test/badge.webp' } },
            },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
        });

        const view = mountView(TeamDetailsView);
        await flushPromises();

        expect(view.get('[data-testid="team-avatar"]').attributes('src')).toBe('https://uploads.test/badge.webp');

        const input = view.get('[data-testid="team-avatar-input"]');
        const file = new File(['badge'], 'badge.png', { type: 'image/png' });

        Object.defineProperty(input.element, 'files', { value: [file], writable: false });
        await input.trigger('change');
        await flushPromises();

        // Its own multipart request rather than a field on the save: a PATCH
        // body carries no files.
        const upload = fetch.mock.calls.find(([url, init]) => String(url).endsWith('/avatar') && init?.method === 'POST')!;
        expect(upload[1]?.body).toBeInstanceOf(FormData);
        expect((upload[1]?.body as FormData).get('avatar')).toBe(file);

        const remove = view.get('[data-testid="remove-team-avatar"]');

        // An icon beside the preview, still named for a screen reader.
        expect(remove.text()).toBe('Remove avatar');
        expect(remove.find('svg').exists()).toBe(true);
        expect(remove.element.parentElement?.querySelector('[data-testid="team-avatar"]')).not.toBeNull();
        expect(view.text()).not.toContain('It is cropped');

        await remove.trigger('click');
        await flushPromises();

        expect(fetch.mock.calls.some(([url, init]) => String(url).endsWith('/avatar') && init?.method === 'DELETE')).toBe(true);
    });

    it('draws a placeholder for a team that has uploaded nothing, and says what is refused', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9/avatar`]: {
                status: 422,
                body: {
                    message: 'The given data was invalid.',
                    errors: { avatar: ['The avatar has invalid image dimensions.'] },
                },
            },
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
        });

        const view = mountView(TeamDetailsView);
        await flushPromises();

        expect(view.get('[data-testid="team-avatar-placeholder"]').text()).toBe('ST');
        expect(view.find('[data-testid="remove-team-avatar"]').exists()).toBe(false);

        const input = view.get('[data-testid="team-avatar-input"]');
        Object.defineProperty(input.element, 'files', {
            value: [new File(['x'], 'tiny.png', { type: 'image/png' })],
            writable: false,
        });
        await input.trigger('change');
        await flushPromises();

        expect(view.get('[data-testid="team-avatar-error"]').text()).toContain('dimensions');
    });

    it('reports an allegiance frozen by a live round', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
        });

        const view = mountView(TeamDetailsView);
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

    it('records the faction this Player is bringing, which is theirs rather than the party\'s', async () => {
        const fetch = stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}/my-faction`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
        });

        const view = mountView(MyFactionView);
        await flushPromises();

        expect((view.get('[data-testid="my-faction"]').element as HTMLSelectElement).value).toBe('3');

        await view.get('[data-testid="my-faction"]').setValue('4');
        await view.get('form').trigger('submit');
        await flushPromises();

        const faction = fetch.mock.calls.find(([url]) => String(url).endsWith('/my-faction'))!;
        expect(JSON.parse(faction[1]?.body as string)).toEqual({ faction_id: 4 });
        expect(view.find('[data-testid="faction-saved"]').exists()).toBe(true);
    });

    it('submits this Player\'s own army list, saying that submitting locks it', async () => {
        const fetch = stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}/army-list`]: { status: 200, body: { data: { army_list: 'Locked in', submitted_at: '2026-09-10T18:30:00Z', is_locked: true } } },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
        });

        const view = mountView(MyArmyListView);
        await flushPromises();

        expect(view.text()).toContain('locks');

        await view.get('[data-testid="army-list"]').setValue('Legion Tactical Squad, 10 models');
        await view.get('[data-testid="submit-army-list"]').trigger('click');
        await flushPromises();

        const submitted = fetch.mock.calls.find(([url]) => String(url).endsWith('/army-list'))!;
        expect(submitted[1]?.method).toBe('PUT');
        expect(JSON.parse(submitted[1]?.body as string)).toEqual({ army_list: 'Legion Tactical Squad, 10 models' });
    });

    it('says a locked list is in, and offers nothing to type into', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: {
                status: 200,
                body: {
                    data: {
                        ...ATTENDEE.data,
                        members: [
                            { id: 12, name: 'Ada Lovelace', faction: { id: 3, name: 'Imperial Fists' }, army_list_locked: true, army_list: 'Legion Tactical Squad' },
                            { id: 13, name: 'Tarik Torgaddon', faction: null, army_list_locked: false, army_list: null },
                        ],
                    },
                },
            },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
        });

        const view = mountView(MyArmyListView);
        await flushPromises();

        expect(view.get('[data-testid="army-list-locked"]').text()).toContain('organiser');
        expect(view.get('[data-testid="army-list-mine"]').text()).toContain('Legion Tactical Squad');
        expect(view.find('[data-testid="army-list"]').exists()).toBe(false);
        expect(view.find('[data-testid="submit-army-list"]').exists()).toBe(false);
    });

    it('corrects the details of a partner who has not answered', async () => {
        const fetch = stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9/members/42`]: { status: 200, body: MY_TEAM },
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: MY_TEAM },
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
        });

        const view = mountView(PartnerView);
        await flushPromises();

        // The address they were invited at is already in the field: the reason
        // to open this screen is usually that it was typed wrong.
        expect((view.get('[data-testid="partner-email"]').element as HTMLInputElement).value).toBe('tarik@exmaple.com');

        await view.get('[data-testid="partner-name"]').setValue('Tarik Torgaddon');
        await view.get('[data-testid="partner-faction"]').setValue('4');
        await view.get('form').trigger('submit');
        await flushPromises();

        const amend = fetch.mock.calls.find(([url, init]) => String(url).endsWith('/members/42') && init?.method === 'PATCH')!;

        expect(JSON.parse(amend[1]?.body as string)).toEqual({
            name: 'Tarik Torgaddon',
            email: 'tarik@exmaple.com',
            faction_id: 4,
        });
        expect(view.find('[data-testid="partner-saved"]').exists()).toBe(true);
    });

    it('says a new invitation follows only once the address has changed', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: MY_TEAM },
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
        });

        const view = mountView(PartnerView);
        await flushPromises();

        const email = view.get('[data-testid="partner-email"]');
        const description = () => (email.attributes('aria-describedby') ?? '')
            .split(' ')
            .filter((id) => id !== '')
            .map((id) => view.find(`[id="${id}"]`).text())
            .join(' ');

        expect(view.get('[data-testid="partner-state"]').text())
            .toBe('They have not accepted their invitation, so you can still edit their details.');
        expect(description()).not.toContain('new invitation');

        await email.setValue('tarik@example.com');

        expect(description()).toContain('A new invitation will be sent to this new email address.');

        await email.setValue('tarik@exmaple.com');

        expect(description()).not.toContain('new invitation');
    });

    it('sends a corrected address, and can send the invitation again', async () => {
        const fetch = stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9/members/42/invite`]: { status: 200 },
            [`/api/events/${EVENT_SLUG}/attendees/9/members/42`]: { status: 200, body: MY_TEAM },
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: MY_TEAM },
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
        });

        const view = mountView(PartnerView);
        await flushPromises();

        await view.get('[data-testid="partner-email"]').setValue('tarik@example.com');
        await view.get('form').trigger('submit');
        await flushPromises();

        const amend = fetch.mock.calls.find(([url, init]) => String(url).endsWith('/members/42') && init?.method === 'PATCH')!;
        expect(JSON.parse(amend[1]?.body as string)).toMatchObject({ email: 'tarik@example.com' });

        await view.get('[data-testid="resend-invite"]').trigger('click');
        await flushPromises();

        const resent = fetch.mock.calls.find(([url]) => String(url).endsWith('/members/42/invite'))!;
        expect(resent[1]?.method).toBe('POST');
        expect(view.get('[data-testid="invite-resent"]').text()).toContain('stopped working');
    });

    it('reports a partner who has claimed their account rather than offering their details', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: {
                status: 200,
                body: {
                    data: {
                        ...MY_TEAM.data,
                        members: [
                            MY_TEAM.data.members[0],
                            { ...MY_TEAM.data.members[1], name: 'Tarik Torgaddon', invite_outstanding: false, email: undefined },
                        ],
                    },
                },
            },
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
        });

        const view = mountView(PartnerView);
        await flushPromises();

        expect(view.get('[data-testid="partner-details"]').text()).toContain('Tarik Torgaddon');
        expect(view.find('form').exists()).toBe(false);
        expect(view.find('[data-testid="resend-invite"]').exists()).toBe(false);
    });

    it('invites somebody into an empty seat', async () => {
        const fetch = stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9/members`]: { status: 201, body: MY_TEAM },
            [`/api/events/${EVENT_SLUG}/attendees/9`]: {
                status: 200,
                body: { data: { ...MY_TEAM.data, members: [MY_TEAM.data.members[0]] } },
            },
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
        });

        const view = mountView(PartnerView);
        await flushPromises();

        await view.get('[data-testid="partner-name"]').setValue('Tarik Torgaddon');
        await view.get('[data-testid="partner-email"]').setValue('tarik@example.com');
        await view.get('form').trigger('submit');
        await flushPromises();

        const invited = fetch.mock.calls.find(([url, init]) => String(url).endsWith('/attendees/9/members') && init?.method === 'POST')!;
        expect(JSON.parse(invited[1]?.body as string)).toEqual({ name: 'Tarik Torgaddon', email: 'tarik@example.com' });
    });

    it('shows a rejected partner address against the field that carries it', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9/members/42`]: {
                status: 422,
                body: {
                    message: 'The given data was invalid.',
                    errors: { email: ['This player has already entered this event.'] },
                },
            },
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: MY_TEAM },
            [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
        });

        const view = mountView(PartnerView);
        await flushPromises();

        await view.get('[data-testid="partner-email"]').setValue('loken@example.com');
        await view.get('form').trigger('submit');
        await flushPromises();

        expect(view.get('[data-testid="partner-email-error"]').text()).toContain('already entered');
    });

    it('enters this team\'s army for the painting vote', async () => {
        const fetch = stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9/painting`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}/attendees/9`]: {
                status: 200,
                body: { data: { ...ATTENDEE.data, painting_entered: false, display_number: null } },
            },
            [`/api/events/${EVENT_SLUG}/polls`]: {
                status: 200,
                body: { data: [{ id: 1, name: 'Best Painted Army', type: 'painting', votes_per_player: 2, opens_at: null, closes_at: null, is_open: false, is_open_for_me: false, my_ballot: [] }] },
            },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ENTERED },
        });

        const view = mountView(PaintedArmyView);
        await flushPromises();

        await view.get('[data-testid="enter-painting"]').trigger('click');
        await flushPromises();

        const entered = fetch.mock.calls.find(([url]) => String(url).endsWith('/attendees/9/painting'))!;
        expect(entered[1]?.method).toBe('PATCH');
        expect(JSON.parse(entered[1]?.body as string)).toEqual({ painting_entered: true });
    });
});
