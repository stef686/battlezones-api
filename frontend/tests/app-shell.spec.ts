import { mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it } from 'vitest';
import type { Router } from 'vue-router';

import { createApiClient } from '@/api';
import { InMemoryTokenStorage } from '@/api/token-storage';
import AppShell from '@/components/AppShell.vue';
import { createAppRouter } from '@/router';

const EVENT_SLUG = 'london-grand-tournament';

let router: Router;
let pinia: ReturnType<typeof createPinia>;

function mountShell() {
    return mount(AppShell, {
        slots: { default: '<p data-testid="screen">a screen</p>' },
        global: {
            plugins: [pinia, router],
        },
    });
}

beforeEach(() => {
    window.localStorage.clear();
    pinia = createPinia();
    setActivePinia(pinia);

    router = createAppRouter();
    createApiClient(router, { baseUrl: 'https://api.test', storage: new InMemoryTokenStorage() });
});

describe('the app shell', () => {
    it('draws the screen it is given', async () => {
        await router.push(`/events/${EVENT_SLUG}`);
        await router.isReady();

        expect(mountShell().get('[data-testid="screen"]').text()).toBe('a screen');
    });

    it('carries no top bar of its own, leaving each screen to name itself', async () => {
        await router.push(`/events/${EVENT_SLUG}/standings`);
        await router.isReady();

        expect(mountShell().find('header').exists()).toBe(false);
    });

    it('carries the tab bar on the screens that belong to an event', async () => {
        await router.push(`/events/${EVENT_SLUG}/standings`);
        await router.isReady();

        const view = mountShell();

        expect(view.find('[data-testid="tab-bar"]').exists()).toBe(true);
        expect(view.get('[data-testid="tab-standings"]').attributes('href')).toBe(`/events/${EVENT_SLUG}/standings`);
    });

    it('draws an icon beside every tab label', async () => {
        await router.push(`/events/${EVENT_SLUG}`);
        await router.isReady();

        const view = mountShell();

        for (const tab of ['event', 'schedule', 'my-game', 'standings']) {
            const icon = view.get(`[data-testid="tab-${tab}"] svg`);

            expect(icon.attributes('aria-hidden')).toBe('true');
        }
    });

    it('leaves the tab bar off a screen with no event to navigate', async () => {
        await router.push('/login');
        await router.isReady();

        expect(mountShell().find('[data-testid="tab-bar"]').exists()).toBe(false);
    });
});
