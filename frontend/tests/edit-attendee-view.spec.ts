import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { keys } from '@/api/keys';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { createAppRouter } from '@/router';
import EditAttendeeView from '@/views/EditAttendeeView.vue';

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

const ATTENDEE = {
    data: {
        id: 9,
        name: 'Sons of Terra',
        allegiance: 'loyalist',
        allegiance_locked: false,
        has_been_paired: false,
        members: [
            { id: 12, name: 'Ada Lovelace', faction: { id: 3, name: 'Imperial Fists' } },
            { id: 13, name: 'Grace Hopper', faction: null },
        ],
        checked_in_at: null,
    },
};

/** Answers by path rather than in order, since the screen reads two things at once. */
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
    return mount(EditAttendeeView as never, ({
        props: { eventSlug: EVENT_SLUG, attendeeId: '9' },
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

describe('the attendee edit screen', () => {
    it('saves the name and allegiance an organiser changes', async () => {
        const fetch = stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        const view = mountView();
        await flushPromises();

        expect(view.get('h1').text()).toBe('Edit attendee');
        expect((view.get('[data-testid="team-name-field"]').element as HTMLInputElement).value).toBe('Sons of Terra');

        await view.get('[data-testid="team-name-field"]').setValue('The Ordo Ludi');
        await view.get('[data-testid="team-allegiance"]').setValue('traitor');
        await view.get('form').trigger('submit');
        await flushPromises();

        const amend = fetch.mock.calls.find(([url, init]) => String(url).endsWith('/attendees/9') && init?.method === 'PATCH')!;
        expect(JSON.parse(amend[1]?.body as string)).toEqual({ name: 'The Ordo Ludi', allegiance: 'traitor' });
        expect(view.find('[data-testid="team-saved"]').exists()).toBe(true);
    });

    it('shows allegiance locked once a round is live, as the player\'s own screen does', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: {
                status: 200,
                body: { data: { ...ATTENDEE.data, allegiance_locked: true } },
            },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        const view = mountView();
        await flushPromises();

        expect((view.get('[data-testid="team-allegiance"]').element as HTMLSelectElement).disabled).toBe(true);
        expect(view.text()).toContain('Frozen now the event has begun');
    });

    it('shows the api\'s refusal against the field it is about', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        const view = mountView();
        await flushPromises();

        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: {
                status: 422,
                body: {
                    message: 'The given data was invalid.',
                    errors: { name: ['The name may not be greater than 255 characters.'] },
                },
            },
        });

        await view.get('form').trigger('submit');
        await flushPromises();

        expect(view.get('[data-testid="team-name-field-error"]').text()).toContain('255 characters');
        expect(view.find('[data-testid="team-saved"]').exists()).toBe(false);
    });

    it('is not there for a reader who does not run the event', async () => {
        const fetch = stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: eventBody({ organise: false }) },
        });

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="missing"]').text().toLowerCase()).toContain('not found');
        expect(view.find('form').exists()).toBe(false);
        expect(fetch.mock.calls.some(([url]) => String(url).endsWith('/attendees/9'))).toBe(false);
    });

    it('retires every screen that shows the team\'s name, not only its own page', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        queryClient.setQueryData(keys.attendees(EVENT_SLUG, '', 1), []);
        queryClient.setQueryData(keys.standings(EVENT_SLUG), []);

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="team-name-field"]').setValue('The Ordo Ludi');
        await view.get('form').trigger('submit');
        await flushPromises();

        expect(queryClient.getQueryState(keys.attendees(EVENT_SLUG, '', 1))?.isInvalidated).toBe(true);
        expect(queryClient.getQueryState(keys.standings(EVENT_SLUG))?.isInvalidated).toBe(true);
    });

    it('stops saying saved once the form is changed again', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        const view = mountView();
        await flushPromises();

        await view.get('form').trigger('submit');
        await flushPromises();

        expect(view.find('[data-testid="team-saved"]').exists()).toBe(true);

        await view.get('[data-testid="team-name-field"]').setValue('The Ordo Ludi');

        expect(view.find('[data-testid="team-saved"]').exists()).toBe(false);
    });

    it('deletes a team that has never been paired, once the organiser confirms', async () => {
        const fetch = stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="delete-attendee"]').trigger('click');

        const dialog = view.get('[data-testid="delete-attendee-confirm"]');
        expect(dialog.text()).toContain('Delete Sons of Terra?');
        expect(dialog.text()).toContain('can\'t be undone');
        expect(fetch.mock.calls.some(([, init]) => init?.method === 'DELETE')).toBe(false);

        stubApi({ [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 204 } });
        const push = vi.spyOn(router, 'push');

        await view.get('[data-testid="delete-attendee-confirm-confirm"]').trigger('click');
        await flushPromises();

        expect(push).toHaveBeenCalledWith({ name: 'attendees', params: { eventSlug: EVENT_SLUG } });
    });

    it('asks about an unnamed team without naming it null', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: { data: { ...ATTENDEE.data, name: null } } },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="delete-attendee"]').trigger('click');

        expect(view.get('[data-testid="delete-attendee-confirm"] h2').text()).toBe('Delete this team?');
    });

    it('offers no delete once the team has been paired', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: { data: { ...ATTENDEE.data, has_been_paired: true } } },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        const view = mountView();
        await flushPromises();

        expect(view.find('[data-testid="delete-attendee"]').exists()).toBe(false);
    });

    it('says why a delete was refused', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        const view = mountView();
        await flushPromises();

        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: {
                status: 422,
                body: {
                    message: 'This team has been paired, so it cannot be deleted. Drop it instead.',
                    errors: { attendee: ['This team has been paired, so it cannot be deleted. Drop it instead.'] },
                },
            },
        });

        await view.get('[data-testid="delete-attendee"]').trigger('click');
        await view.get('[data-testid="delete-attendee-confirm-confirm"]').trigger('click');
        await flushPromises();

        expect(view.get('[data-testid="delete-attendee-error"]').text()).toContain('Drop it instead');
    });

    it('drops a team that has been paired, once the organiser confirms', async () => {
        const PAIRED = { data: { ...ATTENDEE.data, has_been_paired: true, dropped_at: null } };
        const DROPPED = { data: { ...PAIRED.data, dropped_at: '2026-09-12T13:05:00Z' } };
        let fetch = stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: PAIRED },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        queryClient.setQueryData(keys.standings(EVENT_SLUG), []);

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="drop-attendee"]').trigger('click');

        const dialog = view.get('[data-testid="drop-attendee-confirm"]');
        expect(dialog.get('h2').text()).toBe('Drop Sons of Terra?');
        expect(dialog.text()).toContain('They won\'t be paired in later rounds. Results so far stand.');
        expect(dialog.find('[data-testid="drop-attendee-draft-round"]').exists()).toBe(false);
        expect(fetch.mock.calls.some(([url]) => String(url).endsWith('/drop'))).toBe(false);

        fetch = stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9/drop`]: { status: 200, body: DROPPED },
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: DROPPED },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        await view.get('[data-testid="drop-attendee-confirm-confirm"]').trigger('click');
        await flushPromises();

        const drop = fetch.mock.calls.find(([url]) => String(url).endsWith('/attendees/9/drop'));
        expect(drop?.[1]?.method).toBe('POST');
        expect(view.find('[data-testid="drop-attendee"]').exists()).toBe(false);
        // The Standings mark a Dropped team, so they are behind too.
        expect(queryClient.getQueryState(keys.standings(EVENT_SLUG))?.isInvalidated).toBe(true);
    });

    it('warns that a drop leaves a draft round pairing the team as it is', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: {
                status: 200,
                body: { data: { ...ATTENDEE.data, has_been_paired: true, dropped_at: null, paired_in_draft_round: true } },
            },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="drop-attendee"]').trigger('click');

        expect(view.get('[data-testid="drop-attendee-draft-round"]').text())
            .toContain('still paired in the round being drafted');
    });

    it('offers no drop before the team has been paired, nor once it has been dropped', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: ATTENDEE },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        const unpaired = mountView();
        await flushPromises();

        expect(unpaired.find('[data-testid="drop-attendee"]').exists()).toBe(false);

        unpaired.unmount();
        queryClient.clear();
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: {
                status: 200,
                body: { data: { ...ATTENDEE.data, has_been_paired: true, dropped_at: '2026-09-12T13:05:00Z' } },
            },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        const dropped = mountView();
        await flushPromises();

        expect(dropped.find('[data-testid="drop-attendee"]').exists()).toBe(false);
        expect(dropped.find('[data-testid="delete-attendee"]').exists()).toBe(false);
    });

    it('offers undrop in place of drop on a dropped team, and undrops without asking', async () => {
        const DROPPED = { data: { ...ATTENDEE.data, has_been_paired: true, dropped_at: '2026-09-12T13:05:00Z' } };
        const BACK = { data: { ...DROPPED.data, dropped_at: null } };
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: DROPPED },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        const view = mountView();
        await flushPromises();

        expect(view.find('[data-testid="drop-attendee"]').exists()).toBe(false);

        const fetch = stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9/drop`]: { status: 200, body: BACK },
            [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: BACK },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        await view.get('[data-testid="undrop-attendee"]').trigger('click');
        await flushPromises();

        const undrop = fetch.mock.calls.find(([url]) => String(url).endsWith('/attendees/9/drop'));
        expect(undrop?.[1]?.method).toBe('DELETE');
        expect(view.find('[data-testid="undrop-attendee"]').exists()).toBe(false);
        expect(view.find('[data-testid="drop-attendee"]').exists()).toBe(true);
    });

    it('says why an undrop was refused', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: {
                status: 200,
                body: { data: { ...ATTENDEE.data, has_been_paired: true, dropped_at: '2026-09-12T13:05:00Z' } },
            },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        const view = mountView();
        await flushPromises();

        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9/drop`]: {
                status: 422,
                body: { message: 'This team has not been dropped.', errors: { attendee: ['This team has not been dropped.'] } },
            },
        });

        await view.get('[data-testid="undrop-attendee"]').trigger('click');
        await flushPromises();

        expect(view.get('[data-testid="drop-attendee-error"]').text()).toContain('has not been dropped');
    });

    it('warns before undropping that the round being drafted was paired without them', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: {
                status: 200,
                body: { data: { ...ATTENDEE.data, has_been_paired: true, dropped_at: '2026-09-12T13:05:00Z', paired_in_draft_round: false } },
            },
            [`/api/events/${EVENT_SLUG}/rounds`]: {
                status: 200,
                body: { data: [
                    { id: 1, number: 1, name: null, status: 'completed' },
                    { id: 2, number: 2, name: null, status: 'draft' },
                ] },
            },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="undrop-draft-round"]').text())
            .toContain('Round 2 is being drafted without them');
    });

    it('says nothing about drafts when no round is being drafted', async () => {
        stubApi({
            [`/api/events/${EVENT_SLUG}/attendees/9`]: {
                status: 200,
                body: { data: { ...ATTENDEE.data, has_been_paired: true, dropped_at: '2026-09-12T13:05:00Z', paired_in_draft_round: false } },
            },
            [`/api/events/${EVENT_SLUG}/rounds`]: {
                status: 200,
                body: { data: [{ id: 1, number: 1, name: null, status: 'live' }] },
            },
            [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
        });

        const view = mountView();
        await flushPromises();

        expect(view.find('[data-testid="undrop-attendee"]').exists()).toBe(true);
        expect(view.find('[data-testid="undrop-draft-round"]').exists()).toBe(false);
    });
});

/**
 * A doubles team on the day: Ada has claimed her account, Grace was named by
 * her Captain and has not answered her invitation yet.
 */
const PARTY = {
    data: {
        ...ATTENDEE.data,
        members: [
            { id: 12, name: 'Ada Lovelace', faction: { id: 3, name: 'Imperial Fists' }, membership_id: 4, invite_outstanding: false },
            { id: 13, name: 'Grace Hopper', faction: null, membership_id: 5, invite_outstanding: true, email: 'grace@example.com' },
        ],
    },
};

const FACTIONS = {
    data: [
        { id: 3, name: 'Imperial Fists', slug: 'imperial-fists' },
        { id: 7, name: 'Sons of Horus', slug: 'sons-of-horus' },
    ],
};

function partyRoutes(attendee: unknown = PARTY) {
    return {
        [`/api/events/${EVENT_SLUG}/attendees/9`]: { status: 200, body: attendee },
        [`/api/events/${EVENT_SLUG}/factions`]: { status: 200, body: FACTIONS },
        [`/api/events/${EVENT_SLUG}`]: { status: 200, body: ORGANISER },
    };
}

function requestTo(fetch: ReturnType<typeof stubApi>, method: string, suffix: string) {
    return fetch.mock.calls.find(([url, init]) => String(url).split('?')[0]!.endsWith(suffix) && init?.method === method);
}

describe('the players on the attendee edit screen', () => {
    it('swaps an invited player for somebody else by correcting their details', async () => {
        const fetch = stubApi({
            ...partyRoutes(),
            [`/attendees/9/members/5`]: { status: 200, body: PARTY },
        });

        const view = mountView();
        await flushPromises();

        expect((view.get('[data-testid="member-5-email"]').element as HTMLInputElement).value).toBe('grace@example.com');

        await view.get('[data-testid="member-5-name"]').setValue('Alan Turing');
        await view.get('[data-testid="member-5-email"]').setValue('alan@example.com');
        await view.get('[data-testid="member-5-faction"]').setValue('7');
        await view.get('[data-testid="member-5-form"]').trigger('submit');
        await flushPromises();

        const amend = requestTo(fetch, 'PATCH', '/attendees/9/members/5')!;
        expect(JSON.parse(amend[1]?.body as string)).toEqual({ name: 'Alan Turing', email: 'alan@example.com', faction_id: 7 });
        expect(view.find('[data-testid="member-5-saved"]').exists()).toBe(true);
    });

    it('sends an invited player their invitation again, and offers no resend to one who has claimed', async () => {
        const fetch = stubApi({
            ...partyRoutes(),
            [`/attendees/9/members/5/invite`]: { status: 201, body: {} },
        });

        const view = mountView();
        await flushPromises();

        expect(view.find('[data-testid="member-4-resend"]').exists()).toBe(false);

        await view.get('[data-testid="member-5-resend"]').trigger('click');
        await flushPromises();

        expect(requestTo(fetch, 'POST', '/attendees/9/members/5/invite')).toBeDefined();
        expect(view.get('[data-testid="member-5-resent"]').text()).toContain('earlier link has stopped working');
    });

    it('removes a player from the team once the organiser confirms, and rereads the team', async () => {
        const fetch = stubApi({
            ...partyRoutes(),
            [`/attendees/9/members/12`]: { status: 200 },
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="member-4-remove"]').trigger('click');
        expect(view.get('[data-testid="member-4-remove-confirm"] h2').text()).toBe('Remove Ada Lovelace?');
        expect(requestTo(fetch, 'DELETE', '/attendees/9/members/12')).toBeUndefined();

        const readsBefore = fetch.mock.calls.filter(([url]) => String(url).split('?')[0]!.endsWith('/attendees/9')).length;

        await view.get('[data-testid="member-4-remove-confirm-confirm"]').trigger('click');
        await flushPromises();

        expect(requestTo(fetch, 'DELETE', '/attendees/9/members/12')).toBeDefined();
        expect(fetch.mock.calls.filter(([url]) => String(url).split('?')[0]!.endsWith('/attendees/9')).length).toBe(readsBefore + 1);
    });

    it('offers no remove for the only player left, since that is deleting or dropping the team', async () => {
        stubApi(partyRoutes({ data: { ...PARTY.data, members: [PARTY.data.members[0]] } }));

        const view = mountView();
        await flushPromises();

        expect(view.find('[data-testid="member-4"]').exists()).toBe(true);
        expect(view.find('[data-testid="member-4-remove"]').exists()).toBe(false);
    });

    it('adds a player to a team with room for one, and offers no add once the team is full', async () => {
        const routes: Record<string, { status: number; body?: unknown }> = {
            ...partyRoutes({ data: { ...PARTY.data, members: [PARTY.data.members[0]] } }),
            [`/attendees/9/members`]: { status: 201, body: PARTY },
        };
        const fetch = stubApi(routes);

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="add-player-name"]').setValue('Grace Hopper');
        await view.get('[data-testid="add-player-email"]').setValue('grace@example.com');
        await view.get('[data-testid="add-player-faction"]').setValue('7');

        // The team as the API reads it once she is in.
        routes[`/api/events/${EVENT_SLUG}/attendees/9`] = { status: 200, body: PARTY };

        await view.get('[data-testid="add-player-form"]').trigger('submit');
        await flushPromises();

        const add = requestTo(fetch, 'POST', '/attendees/9/members')!;
        expect(JSON.parse(add[1]?.body as string)).toEqual({ name: 'Grace Hopper', email: 'grace@example.com', faction_id: 7 });
        expect(view.find('[data-testid="member-5"]').exists()).toBe(true);
        expect(view.find('[data-testid="add-player-form"]').exists()).toBe(false);
    });

    it('shows the api\'s refusal of a new player against the field it is about', async () => {
        stubApi({
            ...partyRoutes({ data: { ...PARTY.data, members: [PARTY.data.members[0]] } }),
            [`/attendees/9/members`]: {
                status: 422,
                body: { message: 'The given data was invalid.', errors: { email: ['This player has already entered this event.'] } },
            },
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="add-player-email"]').setValue('ada@example.com');
        await view.get('[data-testid="add-player-form"]').trigger('submit');
        await flushPromises();

        expect(view.get('[data-testid="add-player-email-error"]').text()).toContain('already entered');
    });

    it('shows the api\'s refusal of a corrected player against the field it is about', async () => {
        stubApi({
            ...partyRoutes(),
            [`/attendees/9/members/5`]: {
                status: 422,
                body: { message: 'The given data was invalid.', errors: { email: ['This player has already entered this event.'] } },
            },
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="member-5-email"]').setValue('ada@example.com');
        await view.get('[data-testid="member-5-form"]').trigger('submit');
        await flushPromises();

        expect(view.get('[data-testid="member-5-email-error"]').text()).toContain('already entered');
        expect(view.find('[data-testid="member-5-saved"]').exists()).toBe(false);
    });

    it('reports a player who has claimed their account rather than offering to edit them', async () => {
        stubApi(partyRoutes());

        const view = mountView();
        await flushPromises();

        expect(view.get('[data-testid="member-4"]').text()).toContain('Ada Lovelace');
        expect(view.get('[data-testid="member-4"]').text()).toContain('only they can change their details');
        expect(view.find('[data-testid="member-4-form"]').exists()).toBe(false);
    });

    it('keeps what is typed into one player when the team is reread', async () => {
        const ada = { ...PARTY.data.members[0], invite_outstanding: true, email: 'ada@example.com' };
        const routes: Record<string, { status: number; body?: unknown }> = {
            ...partyRoutes({ data: { ...PARTY.data, members: [ada, PARTY.data.members[1]] } }),
            [`/attendees/9/members/5`]: { status: 200, body: PARTY },
        };
        stubApi(routes);

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="member-4-name"]').setValue('Ada King');

        // Somebody else changed Ada's faction meanwhile, so the reread brings her back changed.
        routes[`/api/events/${EVENT_SLUG}/attendees/9`] = {
            status: 200,
            body: { data: { ...PARTY.data, members: [{ ...ada, faction: { id: 7, name: 'Sons of Horus' } }, PARTY.data.members[1]] } },
        };

        await view.get('[data-testid="member-5-form"]').trigger('submit');
        await flushPromises();

        expect((view.get('[data-testid="member-4-name"]').element as HTMLInputElement).value).toBe('Ada King');
    });

    it('stops saying a player is saved once their details are changed again', async () => {
        stubApi({
            ...partyRoutes(),
            [`/attendees/9/members/5`]: { status: 200, body: PARTY },
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="member-5-name"]').setValue('Grace Hopper');
        await view.get('[data-testid="member-5-form"]').trigger('submit');
        await flushPromises();
        expect(view.find('[data-testid="member-5-saved"]').exists()).toBe(true);

        await view.get('[data-testid="member-5-name"]').setValue('Grace Brewster');
        expect(view.find('[data-testid="member-5-saved"]').exists()).toBe(false);
    });

    it('says only what happened last, saved or resent', async () => {
        stubApi({
            ...partyRoutes(),
            [`/attendees/9/members/5`]: { status: 200, body: PARTY },
            [`/attendees/9/members/5/invite`]: { status: 201, body: {} },
        });

        const view = mountView();
        await flushPromises();

        await view.get('[data-testid="member-5-form"]').trigger('submit');
        await flushPromises();
        await view.get('[data-testid="member-5-resend"]').trigger('click');
        await flushPromises();

        expect(view.find('[data-testid="member-5-resent"]').exists()).toBe(true);
        expect(view.find('[data-testid="member-5-saved"]').exists()).toBe(false);

        await view.get('[data-testid="member-5-form"]').trigger('submit');
        await flushPromises();

        expect(view.find('[data-testid="member-5-saved"]').exists()).toBe(true);
        expect(view.find('[data-testid="member-5-resent"]').exists()).toBe(false);
    });
});
