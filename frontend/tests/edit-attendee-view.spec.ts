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
});
