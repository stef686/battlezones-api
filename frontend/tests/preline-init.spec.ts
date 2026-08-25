import { afterEach, describe, expect, it, vi } from 'vitest';
import { createMemoryHistory, createRouter } from 'vue-router';

import { usePrelineInit } from '@/composables/usePrelineInit';

const home = { path: '/', component: { template: '<p>home</p>' } };
const away = { path: '/away', component: { template: '<p>away</p>' } };

function routerWithPreline() {
    const router = createRouter({ history: createMemoryHistory(), routes: [home, away] });

    usePrelineInit(router);

    return router;
}

afterEach(() => {
    delete window.HSDropdown;
    delete window.HSOverlay;
});

describe('re-binding Preline after a navigation', () => {
    it('rescans every loaded plugin once the new screen is in the DOM', async () => {
        const dropdown = vi.fn();
        const overlay = vi.fn();

        window.HSDropdown = { autoInit: dropdown };
        window.HSOverlay = { autoInit: overlay };

        const router = routerWithPreline();

        await router.push('/');
        await vi.waitFor(() => expect(dropdown).toHaveBeenCalledTimes(1));
        expect(overlay).toHaveBeenCalledTimes(1);

        // Nothing reloads in an SPA, so the screen after this one carries
        // `data-hs-*` attributes that nobody has bound to yet.
        await router.push('/away');
        await vi.waitFor(() => expect(dropdown).toHaveBeenCalledTimes(2));
        expect(overlay).toHaveBeenCalledTimes(2);
    });

    it('navigates fine when the bundles are not loaded at all', async () => {
        const router = routerWithPreline();

        await expect(router.push('/away')).resolves.toBeUndefined();
    });
});
