import { expect, test, type Page } from '@playwright/test';

import { resetWorld } from './reset';

const EVENT_SLUG = 'end-to-end-open';
const ORGANISER = { email: 'organiser@battlezones.test', password: 'end-to-end-password' };
const PLAYER = { email: 'player@battlezones.test', password: 'end-to-end-password' };

test.beforeEach(() => {
    resetWorld();
});

async function logIn(page: Page, who: { email: string; password: string }): Promise<void> {
    await page.goto('/login');
    await page.getByTestId('email').fill(who.email);
    await page.getByTestId('password').fill(who.password);
    await page.getByTestId('submit-login').click();

    await expect(page).not.toHaveURL(/\/login/);
}

test('an Organiser reviews the draft round, publishes it, and takes it back', async ({ page }) => {
    await logIn(page, ORGANISER);
    await page.goto(`/events/${EVENT_SLUG}/organise`);

    // What is holding up the next Round, first: table 7 has not reported.
    await expect(page.getByTestId('outstanding-count')).toHaveText('1 table to go');

    // The seeder leaves Round 2 paired but unpublished, which is the review.
    const review = page.locator('[data-testid^="review-"]').first();
    await expect(review.getByTestId('allegiance-loyalist')).toBeVisible();
    await expect(review.getByTestId('allegiance-traitor')).toBeVisible();

    // Every control is full width and thumb-sized: this is used standing up.
    const publish = page.getByTestId('publish-round');
    await expect(publish).toContainText('Round 2');

    const button = await publish.boundingBox();
    const viewport = page.viewportSize()!;
    expect(button!.height).toBeGreaterThanOrEqual(44);
    expect(button!.width).toBeGreaterThan(viewport.width * 0.6);

    await publish.click();

    // Published: the draft is gone, and pairing the next one is now the offer.
    await expect(page.getByTestId('generate-round')).toBeVisible();
    await expect(page.getByTestId('publish-round')).toHaveCount(0);

    // Round 2 has not been played, so it can still be taken back.
    await page.getByTestId('unpublish-round').click();
    await expect(page.getByTestId('publish-round')).toBeVisible();
});

test('pairing is refused, in the API\'s own words, while a table has not reported', async ({ page }) => {
    await logIn(page, ORGANISER);
    await page.goto(`/events/${EVENT_SLUG}/organise`);

    await page.getByTestId('publish-round').click();
    await expect(page.getByTestId('generate-round')).toBeVisible();

    await page.getByTestId('generate-round').click();

    await expect(page.getByTestId('organise-problem')).toContainText('results outstanding');
});

test('a published round reaches the Players it was published to', async ({ page }) => {
    await logIn(page, ORGANISER);
    await page.goto(`/events/${EVENT_SLUG}/organise`);
    await page.getByTestId('publish-round').click();
    await expect(page.getByTestId('generate-round')).toBeVisible();

    // A different reader entirely, arriving fresh.
    await page.evaluate(() => window.localStorage.clear());
    await page.goto(`/events/${EVENT_SLUG}/rounds`);

    await expect(page.locator('[data-testid^="round-"]')).toHaveCount(2);
    await expect(page.getByTestId('round-draft')).toHaveCount(0);
});

test('a Player has no organiser screen to find', async ({ page }) => {
    await logIn(page, PLAYER);
    await page.goto(`/events/${EVENT_SLUG}/organise`);

    await expect(page.getByTestId('missing')).toBeVisible();
    await expect(page.getByTestId('generate-round')).toHaveCount(0);
    await expect(page.getByTestId('publish-round')).toHaveCount(0);
});
