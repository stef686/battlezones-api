import { VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { createAppRouter } from '@/router';
import { useSessionStore } from '@/stores/session';
import ClaimView from '@/views/ClaimView.vue';
import InviteView from '@/views/InviteView.vue';

const INVITE = {
    id: 7,
    role: 'captain',
    email: 'captain@example.com',
    name: 'Ada Lovelace',
    is_claimed: false,
    attendee_id: null,
    event: {
        slug: 'london-grand-tournament',
        name: 'London Grand Tournament',
        starts_at: '2026-09-12T09:00:00+00:00',
        ends_at: '2026-09-13T18:00:00+00:00',
    },
    expires_at: '2026-09-13T18:00:00+00:00',
    revoked_at: null,
};

const PROFILE = {
    id: 12,
    public_name: 'Ada Lovelace',
    is_claimed: false,
    email_verified: false,
    unread_notifications_count: 0,
};

interface StubResponse {
    status: number;
    body?: unknown;
}

function stubFetch(...responses: StubResponse[]) {
    const fetch = vi.fn();

    responses.forEach(({ status, body }) => {
        fetch.mockResolvedValueOnce({
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

function mountView(component: unknown, props: Record<string, unknown> = {}) {
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

    await router.push('/');
    await router.isReady();
});

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('the invitation landing screen', () => {
    it('shows the Event and who it was sent to before asking for anything', async () => {
        stubFetch({ status: 200, body: { data: INVITE } });

        const view = mountView(InviteView, { token: 'plain-token' });
        await flushPromises();

        expect(view.get('[data-testid="invite-event"]').text()).toBe('London Grand Tournament');
        expect(view.get('[data-testid="invite-email"]').text()).toBe('captain@example.com');
        expect(view.get('[data-testid="invite-dates"]').text()).not.toBe('');

        // Nothing has been asked for yet: no password field on this screen.
        expect(view.find('input[type="password"]').exists()).toBe(false);
    });

    it('lets a Player in on the Invite alone and lands them on their game', async () => {
        stubFetch(
            { status: 200, body: { data: INVITE } },
            { status: 200, body: { token: 'invited', expires_at: '2026-09-13T18:00:00Z' } },
            { status: 200, body: { data: PROFILE } },
        );

        const view = mountView(InviteView, { token: 'plain-token' });
        await flushPromises();

        await view.get('[data-testid="enter-with-invite"]').trigger('click');
        await flushPromises();

        await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('my-game'));
        expect(router.currentRoute.value.params.eventSlug).toBe('london-grand-tournament');

        // Remembered, because claiming later has no other way back to the token.
        expect(useSessionStore().invite).toEqual({ token: 'plain-token', eventSlug: 'london-grand-tournament' });
    });

    it('explains a dead invitation and offers the way past it', async () => {
        stubFetch({
            status: 410,
            body: { message: 'This invitation has been used to set up an account. Log in to continue.', code: 'invite_revoked' },
        });

        const view = mountView(InviteView, { token: 'spent' });
        await flushPromises();

        expect(view.get('[data-testid="invite-dead"]').text()).toContain('Log in to continue');
        expect(view.find('[data-testid="invite-login-link"]').exists()).toBe(true);
        expect(view.find('[data-testid="enter-with-invite"]').exists()).toBe(false);
    });

    it('can go straight to setting a password, remembering the token on the way', async () => {
        stubFetch({ status: 200, body: { data: INVITE } });

        const view = mountView(InviteView, { token: 'plain-token' });
        await flushPromises();

        await view.get('[data-testid="claim-from-invite"]').trigger('click');
        await flushPromises();

        await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('claim'));
        expect(useSessionStore().invite?.token).toBe('plain-token');
    });
});

describe('the claim screen', () => {
    beforeEach(async () => {
        useSessionStore().rememberInvite({ token: 'plain-token', eventSlug: 'london-grand-tournament' });

        await router.push('/claim');
    });

    it('sets a password and returns the Player to their Event, unrestricted', async () => {
        const fetch = stubFetch(
            { status: 201, body: { token: 'claimed', expires_at: null } },
            { status: 200, body: { data: { ...PROFILE, is_claimed: true } } },
        );

        const view = mountView(ClaimView);

        await view.get('[data-testid="claim-password"]').setValue('a-good-password');
        await view.get('[data-testid="claim-password-confirmation"]').setValue('a-good-password');
        await view.get('form').trigger('submit');
        await flushPromises();

        expect(fetch.mock.calls[0]![0]).toBe('https://api.test/api/invites/plain-token/claim');
        await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('my-game'));

        const session = useSessionStore();
        expect(session.invite).toBeNull();
        expect(session.isUnclaimed).toBe(false);
    });

    it('shows the API\'s own words against the field that failed', async () => {
        stubFetch({
            status: 422,
            body: { message: 'The given data was invalid.', errors: { password: ['The password field confirmation does not match.'] } },
        });

        const view = mountView(ClaimView);

        await view.get('[data-testid="claim-password"]').setValue('a-good-password');
        await view.get('[data-testid="claim-password-confirmation"]').setValue('mistyped');
        await view.get('form').trigger('submit');
        await flushPromises();

        expect(view.get('[data-testid="claim-password-error"]').text()).toContain('does not match');
        expect(router.currentRoute.value.name).toBe('claim');
    });

    it('says what to do when the session has no invitation to claim', () => {
        useSessionStore().forgetInvite();

        const view = mountView(ClaimView);

        expect(view.get('[data-testid="claim-needs-invite"]').text()).toContain('invitation email');
        expect(view.find('form').exists()).toBe(false);
    });
});
