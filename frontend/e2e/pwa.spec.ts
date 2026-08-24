import { appendFileSync, readFileSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';

import { expect, test } from '@playwright/test';

const EVENT_SLUG = 'end-to-end-open';
const SERVICE_WORKER = resolve(process.cwd(), 'dist/sw.js');

/** Wait for the worker to be the one serving this page, not merely installed. */
async function serviceWorkerControls(page: import('@playwright/test').Page): Promise<void> {
    await page.waitForFunction(() => navigator.serviceWorker.controller !== null, null, { timeout: 15_000 });
}

test('the app installs: manifest, icons, and a standalone display', async ({ page, request }) => {
    await page.goto(`/events/${EVENT_SLUG}`);

    const href = await page.locator('link[rel="manifest"]').getAttribute('href');
    expect(href).toBeTruthy();

    const manifest = await (await request.get(`http://localhost:4173${href}`)).json();

    expect(manifest.name).toBe('Battlezones');
    expect(manifest.display).toBe('standalone');
    expect(manifest.theme_color).toBe('#0d1014');
    expect(manifest.background_color).toBe('#0d1014');

    // The sizes an installer asks for, plus a maskable one so a launcher that
    // crops to a circle does not cut the mark in half.
    const icons: { sizes: string; purpose?: string }[] = manifest.icons;
    expect(icons.map((icon) => icon.sizes)).toEqual(expect.arrayContaining(['192x192', '512x512']));
    expect(icons.some((icon) => icon.purpose === 'maskable')).toBe(true);

    for (const icon of manifest.icons) {
        expect((await request.get(`http://localhost:4173${icon.src}`)).status()).toBe(200);
    }

    // iOS installs from the home screen and reads these rather than the
    // manifest, so they are what makes it standalone there.
    await expect(page.locator('link[rel="apple-touch-icon"]')).toHaveCount(1);
    await expect(page.locator('meta[name="apple-mobile-web-app-capable"]')).toHaveAttribute('content', 'yes');
});

test('the shell is precached and API responses never are', async ({ page, context }) => {
    await page.goto(`/events/${EVENT_SLUG}`);
    await serviceWorkerControls(page);

    // Move around so every screen has had its chance to put something in a
    // cache it should not.
    await page.goto(`/events/${EVENT_SLUG}/attendees`);
    await page.goto(`/events/${EVENT_SLUG}/standings`);

    const cached: string[] = await page.evaluate(async () => {
        const names = await caches.keys();
        const urls: string[] = [];

        for (const name of names) {
            const requests = await (await caches.open(name)).keys();
            urls.push(...requests.map((request) => request.url));
        }

        return urls;
    });

    // The hard rule: server state belongs to the query layer, and a second
    // cache disagreeing with it is a stale standings table at a venue.
    expect(cached.filter((url) => url.includes('/api/'))).toEqual([]);
    expect(cached.some((url) => url.endsWith('/index.html') || url.includes('/assets/'))).toBe(true);

    // Precached means the shell still renders with the network gone — the
    // screen then says it cannot reach the API, which is the honest answer.
    await context.setOffline(true);
    await page.goto(`/events/${EVENT_SLUG}`);
    await expect(page.locator('#app')).toBeAttached();
    await context.setOffline(false);
});

test('a new deployment offers a reload rather than taking one', async ({ page }) => {
    const original = readFileSync(SERVICE_WORKER, 'utf8');

    await page.goto(`/events/${EVENT_SLUG}`);
    await serviceWorkerControls(page);

    await expect(page.getByTestId('update-available')).toHaveCount(0);

    try {
        // What a deploy looks like to a browser: the worker at the same URL
        // is not the bytes it installed.
        appendFileSync(SERVICE_WORKER, '\n// deployed again\n');

        await page.evaluate(async () => {
            const registration = await navigator.serviceWorker.getRegistration();

            await registration?.update();
        });

        // Offered, not taken: a Player halfway through a result must not have
        // the page swapped under them.
        await expect(page.getByTestId('update-available')).toBeVisible({ timeout: 15_000 });
        await expect(page).toHaveURL(new RegExp(`/events/${EVENT_SLUG}$`));

        await page.getByTestId('take-update').click();

        // One navigation, and the new worker is the one serving the page.
        await serviceWorkerControls(page);
        await expect(page.getByTestId('update-available')).toHaveCount(0);
    } finally {
        writeFileSync(SERVICE_WORKER, original);
    }
});
