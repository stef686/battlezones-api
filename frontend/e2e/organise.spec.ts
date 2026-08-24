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
    await expect(page.getByTestId('draft-badge')).toHaveCount(0);
});

test('a Player has no organiser screen to find', async ({ page }) => {
    await logIn(page, PLAYER);
    await page.goto(`/events/${EVENT_SLUG}/organise`);

    await expect(page.getByTestId('missing')).toBeVisible();
    await expect(page.getByTestId('generate-round')).toHaveCount(0);
    await expect(page.getByTestId('publish-round')).toHaveCount(0);
});

test('an Organiser swaps two pairings, seeing the result before committing', async ({ page }) => {
    await logIn(page, ORGANISER);
    await page.goto(`/events/${EVENT_SLUG}/organise`);

    const before = page.locator('[data-testid^="review-"]').first();
    await expect(before).toContainText('Ada Lovelace and partner');
    await expect(before).toContainText('Grace Hopper and partner');

    await page.locator('[data-testid^="swap-"]').first().click();
    await expect(page.getByTestId('swap-prompt')).toBeVisible();

    await page.locator('[data-testid^="swap-"]').nth(1).click();

    // The consequence is shown before it is committed: table 12 keeps Ada and
    // faces the other Traitor instead.
    const preview = page.getByTestId('swap-preview');
    await expect(preview).toContainText('Ada Lovelace and partner');
    await expect(preview).toContainText('Konrad Curze and partner');
    await expect(page.getByTestId('swap-unopposed')).toHaveCount(0);

    await page.getByTestId('confirm-swap').click();
    await expect(page.getByTestId('swap-preview')).toHaveCount(0);

    // And the Round now says what the preview said it would.
    const after = page.locator('[data-testid^="review-"]').first();
    await expect(after).toContainText('Ada Lovelace and partner');
    await expect(after).toContainText('Konrad Curze and partner');
});

test('an Organiser enters the victory points for a Bye', async ({ page }) => {
    await logIn(page, ORGANISER);
    await page.goto(`/events/${EVENT_SLUG}/organise`);

    const bye = page.locator('[data-testid^="bye-"]').first();
    await expect(bye).toContainText('Ferrus Manus and partner');
    await expect(bye).toContainText('win');

    await page.locator('[data-testid^="bye-score-"]').first().fill('60');
    await page.locator('[data-testid^="save-bye-"]').first().click();
    await expect(page.locator('[data-testid^="bye-saved-"]').first()).toBeVisible();

    // A Bye counts as a win, so its holder carries match points in the
    // standings whether or not anyone has entered its victory points.
    await page.goto(`/events/${EVENT_SLUG}/standings`);
    const row = page.locator('[data-testid^="standing-"]', { hasText: 'Ferrus Manus and partner' });
    await expect(row.getByTestId('match-points')).toHaveText('3');
    await expect(row.getByTestId('victory-points')).toHaveText('60');
});
