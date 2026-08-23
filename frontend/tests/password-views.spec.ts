import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import { createAppRouter } from '@/router';
import { useSessionStore } from '@/stores/session';
import ForgotPasswordView from '@/views/ForgotPasswordView.vue';
import ResetPasswordView from '@/views/ResetPasswordView.vue';

const PROFILE = {
    id: 12,
    public_name: 'Ada Lovelace',
    email: 'ada@example.com',
    is_claimed: true,
    email_verified: true,
    unread_notifications_count: 0,
};

const NEUTRAL_ANSWER = 'If a user with that email address exists, we have sent a password reset link. Please check your email.';

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

function mountView(component: unknown) {
    return mount(component as never, { global: { plugins: [pinia, router] } });
}

beforeEach(async () => {
    window.localStorage.clear();
    pinia = createPinia();
    setActivePinia(pinia);

    router = createAppRouter();
    createApiClient(router, { baseUrl: 'https://api.test', storage: new InMemoryTokenStorage() });

    await router.push('/login');
    await router.isReady();
});

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('asking for a reset link', () => {
    it('sends the address and repeats the API\'s deliberately uninformative answer', async () => {
        const fetch = stubFetch({ status: 200, body: { message: NEUTRAL_ANSWER } });

        const view = mountView(ForgotPasswordView);

        await view.get('[data-testid="forgot-email"]').setValue('ada@example.com');
        await view.get('form').trigger('submit');
        await flushPromises();

        const [url, init] = fetch.mock.calls[0]!;
        expect(url).toBe('https://api.test/api/auth/forgot-password');
        expect(JSON.parse((init as RequestInit).body as string)).toEqual({ email: 'ada@example.com' });

        // The same answer either way: anything else lets a stranger test addresses.
        expect(view.get('[data-testid="reset-link-sent"]').text()).toBe(NEUTRAL_ANSWER);
        expect(view.find('form').exists()).toBe(false);
    });

    it('says when it was throttled, rather than looking as though it worked', async () => {
        stubFetch({ status: 429, body: { message: 'Too Many Attempts.' } });

        const view = mountView(ForgotPasswordView);

        await view.get('[data-testid="forgot-email"]').setValue('ada@example.com');
        await view.get('form').trigger('submit');
        await flushPromises();

        expect(view.get('[data-testid="forgot-error"]').text()).toContain('Too Many Attempts');
        expect(view.find('[data-testid="reset-link-sent"]').exists()).toBe(false);
    });
});

describe('following a reset link', () => {
    async function openLink(query: string) {
        await router.push(`/reset-password${query}`);

        return mountView(ResetPasswordView);
    }

    it('resets the password and signs the reader straight in', async () => {
        const fetch = stubFetch(
            { status: 200, body: { message: 'Your password has been reset. You may now log in with your new password.' } },
            { status: 200, body: { token: 'fresh', expires_at: null } },
            { status: 200, body: { data: PROFILE } },
        );

        const view = await openLink('?token=reset-token&email=ada%40example.com');

        await view.get('[data-testid="reset-password"]').setValue('a-new-password');
        await view.get('[data-testid="reset-password-confirmation"]').setValue('a-new-password');
        await view.get('form').trigger('submit');
        await flushPromises();

        expect(JSON.parse((fetch.mock.calls[0]![1] as RequestInit).body as string)).toEqual({
            token: 'reset-token',
            email: 'ada@example.com',
            password: 'a-new-password',
            password_confirmation: 'a-new-password',
        });

        // Signed in with what was just set: retyping it is how a recovery flow
        // on a phone loses the person it exists for.
        expect(fetch.mock.calls[1]![0]).toBe('https://api.test/api/login/token');
        expect(useSessionStore().viewer?.id).toBe(12);
    });

    it('shows a spent token against the address, the way the API reports it', async () => {
        stubFetch({
            status: 422,
            body: { message: 'The given data was invalid.', errors: { email: ['This password reset token is invalid or has expired.'] } },
        });

        const view = await openLink('?token=stale&email=ada%40example.com');

        await view.get('[data-testid="reset-password"]').setValue('a-new-password');
        await view.get('[data-testid="reset-password-confirmation"]').setValue('a-new-password');
        await view.get('form').trigger('submit');
        await flushPromises();

        expect(view.get('[data-testid="reset-token-error"]').text()).toContain('invalid or has expired');
    });

    it('sends the reader to log in when the reset landed but the sign-in did not', async () => {
        stubFetch(
            { status: 200, body: { message: 'Your password has been reset. You may now log in with your new password.' } },
            { status: 429, body: { message: 'Too Many Attempts.' } },
        );

        const view = await openLink('?token=reset-token&email=ada%40example.com');

        await view.get('[data-testid="reset-password"]').setValue('a-new-password');
        await view.get('[data-testid="reset-password-confirmation"]').setValue('a-new-password');
        await view.get('form').trigger('submit');
        await flushPromises();

        await vi.waitFor(() => expect(router.currentRoute.value.name).toBe('login'));
        expect(router.currentRoute.value.query.reset).toBe('1');
    });

    it('explains a truncated link instead of failing at the API', async () => {
        const fetch = stubFetch();

        const view = await openLink('?email=ada%40example.com');

        expect(view.get('[data-testid="reset-link-broken"]').text()).toContain('cut long links short');
        expect(view.find('form').exists()).toBe(false);
        expect(fetch).not.toHaveBeenCalled();
    });
});
