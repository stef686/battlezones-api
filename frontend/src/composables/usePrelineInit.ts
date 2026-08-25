import { nextTick } from 'vue';
import type { Router } from 'vue-router';

/**
 * Preline's interactive components are progressive enhancement over plain
 * markup: each plugin scans the document on load and binds to the `data-hs-*`
 * attributes it finds.
 *
 * A single-page app breaks that assumption. Nothing reloads, so a screen
 * rendered after the first paint carries attributes nobody bound to and its
 * dropdowns simply do not open. Re-scanning once per navigation is what
 * Preline's SPA guidance prescribes, and a plugin skips elements already in
 * its collection, so the re-scan is cheap rather than a rebind.
 *
 * Plugins are listed rather than pulled in wholesale: `import 'preline'` is
 * the umbrella bundle and drags apexcharts, datatables, dropzone and a date
 * picker in with it — half a megabyte the app never uses, over venue wifi.
 * Add a plugin here, and its `preline/plugins/*` import below, when a screen
 * first needs it.
 */
/** Read at call time: the bundles publish themselves during their own load. */
function activePlugins(): (PrelinePlugin | undefined)[] {
    return [window.HSCollapse, window.HSDropdown, window.HSOverlay, window.HSTabs, window.HSTooltip];
}

export function usePrelineInit(router: Router): void {
    // `afterEach` fires before Vue has patched the new view into the DOM, so
    // the scan waits a tick or it finds the screen it has already bound.
    router.afterEach(() => {
        void nextTick(() => {
            for (const plugin of activePlugins()) {
                plugin?.autoInit?.();
            }
        });
    });
}
