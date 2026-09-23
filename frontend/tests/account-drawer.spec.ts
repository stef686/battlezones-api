import { QueryClient, VueQueryPlugin } from '@tanstack/vue-query';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import AppShell from '@/components/AppShell.vue';
import { createAppRouter } from '@/router';
import { useSessionStore, type Viewer } from '@/stores/session';

let router: Router;
let pinia: ReturnType<typeof createPinia>;
let queryClient: QueryClient;
let storage: InMemoryTokenStorage;

function signIn(overrides: Partial<Viewer> = {}): void {
    storage.write('a-token');
    useSessionStore().viewer = {
        id: 4,
        public_name: 'Ada Lovelace',
        email: 'ada@example.test',
        is_claimed: true,
        email_verified: true,
        unread_notifications_count: 0,
        ...overrides,
    };
}

async function mountShell() {
    const view = mount(AppShell, {
        slots: { default: '<p>a screen</p>' },
        attachTo: document.body,
        global: {
            plugins: [pinia, router, [VueQueryPlugin, { queryClient }]],
        },
    });

    await flushPromises();

    return view;
}

async function openDrawer() {
    const view = await mountShell();

    await view.get('[data-testid="tab-account"]').trigger('click');

    return view;
}

beforeEach(async () => {
    pinia = createPinia();
    setActivePinia(pinia);
    queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });
    storage = new InMemoryTokenStorage();

    router = createAppRouter();
    createApiClient(router, { baseUrl: 'https://api.test', storage });

    vi.stubGlobal('fetch', vi.fn(() => Promise.resolve({
        ok: true,
        status: 204,
        headers: new Headers(),
        json: () => Promise.resolve(null),
    })));

    await router.push('/login');
    await router.isReady();
});

afterEach(() => {
    vi.unstubAllGlobals();
    document.body.innerHTML = '';
});

describe('the account drawer', () => {
    it('opens from the avatar and names the signed-in viewer', async () => {
        signIn();

        const view = await openDrawer();
        const drawer = view.get('[data-testid="account-drawer"]');

        expect(drawer.attributes('open')).toBeDefined();
        expect(drawer.get('[data-testid="account-drawer-name"]').text()).toBe('Ada Lovelace');
        expect(drawer.get('[data-testid="avatar-placeholder"]').text()).toBe('AL');
    });

    it('takes focus to its close button, never to logging out', async () => {
        signIn();

        const view = await openDrawer();
        const close = view.get('[data-testid="account-drawer-close"]');

        expect(close.attributes('autofocus')).toBeDefined();
        expect(close.text()).toBe('Close');
    });

    it('closes from its close button and tells the avatar it has', async () => {
        signIn();

        const view = await openDrawer();
        await view.get('[data-testid="account-drawer-close"]').trigger('click');

        expect(view.get('[data-testid="account-drawer"]').attributes('open')).toBeUndefined();
        expect(view.get('[data-testid="tab-account"]').attributes('aria-expanded')).toBe('false');
    });

    it('keeps up when the browser closes it, as it does on Esc', async () => {
        signIn();

        const view = await openDrawer();
        (view.get('[data-testid="account-drawer"]').element as HTMLDialogElement).close();
        await flushPromises();

        expect(view.get('[data-testid="tab-account"]').attributes('aria-expanded')).toBe('false');
    });

    it('closes on a tap on the dimmed page behind it', async () => {
        signIn();

        const view = await openDrawer();
        await view.get('[data-testid="account-drawer"]').trigger('click');

        expect(view.get('[data-testid="account-drawer"]').attributes('open')).toBeUndefined();
    });

    it('stays open on a tap inside it', async () => {
        signIn();

        const view = await openDrawer();
        await view.get('[data-testid="account-drawer-name"]').trigger('click');

        expect(view.get('[data-testid="account-drawer"]').attributes('open')).toBeDefined();
    });

    it('lists every destination under its heading, the same for every viewer', async () => {
        signIn();

        const view = await openDrawer();
        const sections = view.findAll('[data-testid="account-drawer-section"]').map((section) => ({
            heading: section.get('h2').text(),
            items: section.findAll('li').map((item) => item.text()),
        }));

        expect(sections).toEqual([
            { heading: 'Player', items: ['Events', 'Clubs', 'Friends'] },
            { heading: 'Organiser', items: ['Events', 'Run an event'] },
            { heading: 'Account', items: ['Details', 'Settings'] },
        ]);
    });

    it('telegraphs destinations that do not exist yet rather than pretending they work', async () => {
        signIn();

        const view = await openDrawer();

        for (const item of view.findAll('[data-testid="account-drawer-section"] li > *')) {
            expect(item.attributes('aria-disabled')).toBe('true');
            expect(item.attributes('href')).toBeUndefined();
            expect(item.attributes('tabindex')).toBe('-1');
        }
    });

    it('logs out: revokes the token, forgets the viewer and what was read for them, and lands on sign in', async () => {
        signIn();
        await router.push('/events/london-grand-tournament');
        queryClient.setQueryData(['events', 'london-grand-tournament', 'my-team'], { secret: true });

        const view = await openDrawer();
        await view.get('[data-testid="account-drawer-logout"]').trigger('click');
        await flushPromises();

        expect(fetch).toHaveBeenCalledWith('https://api.test/api/auth/logout', expect.objectContaining({ method: 'POST' }));
        expect(storage.read()).toBeNull();
        expect(useSessionStore().viewer).toBeNull();
        expect(queryClient.getQueryData(['events', 'london-grand-tournament', 'my-team'])).toBeUndefined();
        expect(router.currentRoute.value.name).toBe('login');
        expect(view.find('[data-testid="account-drawer"]').exists()).toBe(false);
    });

    it('logs out of this device even when the API cannot be reached', async () => {
        signIn();
        vi.stubGlobal('fetch', vi.fn().mockRejectedValue(new TypeError('Failed to fetch')));

        const view = await openDrawer();
        await view.get('[data-testid="account-drawer-logout"]').trigger('click');
        await flushPromises();

        expect(storage.read()).toBeNull();
        expect(useSessionStore().viewer).toBeNull();
        expect(router.currentRoute.value.name).toBe('login');
    });

    it('warns an unclaimed viewer that logging out leaves only the invite email to get back in', async () => {
        signIn({ is_claimed: false });

        const view = await openDrawer();
        const warning = view.get('[data-testid="account-drawer-unclaimed"]');

        expect(warning.text()).toContain('invite email');
        expect(warning.get('a').attributes('href')).toBe('/claim');
        expect(view.find('[data-testid="account-drawer-logout"]').exists()).toBe(true);
    });

    it('does not warn a viewer who has set a password', async () => {
        signIn();

        const view = await openDrawer();

        expect(view.find('[data-testid="account-drawer-unclaimed"]').exists()).toBe(false);
    });
});
