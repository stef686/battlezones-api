import { expect, test } from '@playwright/test';

import { resetWorld } from './reset';

const EVENT_SLUG = 'end-to-end-open';
const PLAYER = { email: 'player@battlezones.test', password: 'end-to-end-password' };
const TABLE_NUMBER = '7';

/**
 * Reset the world before each run: the flow submits a result, and a Game that
 * is already scored answers the next run with a conflict.
 */
test.beforeEach(() => {
    resetWorld();
});

test('a Player logs in, finds their table, submits a result, and sees the standings move', async ({ page }) => {
    await page.goto(`/events/${EVENT_SLUG}/my-game`);

    // Not logged in: the guard sends them to login and remembers where they were going.
    await expect(page).toHaveURL(/\/login\?redirect=/);

    await page.getByTestId('email').fill(PLAYER.email);
    await page.getByTestId('password').fill(PLAYER.password);
    await page.getByTestId('submit-login').click();

    // ...and back to the route they asked for, not a home screen.
    await expect(page).toHaveURL(new RegExp(`/events/${EVENT_SLUG}/my-game`));

    const tableNumber = page.getByTestId('table-number');
    await expect(tableNumber).toHaveText(TABLE_NUMBER);
    await expect(page.getByTestId('opponent')).toContainText('Grace Hopper');

    // The table number is the dominant element: it should be far larger than
    // the opponent's name, which is the next thing a Player looks for.
    const tableSize = await tableNumber.evaluate((node) => parseFloat(getComputedStyle(node).fontSize));
    const opponentSize = await page.getByTestId('opponent')
        .evaluate((node) => parseFloat(getComputedStyle(node).fontSize));
    expect(tableSize).toBeGreaterThan(opponentSize * 2);

    await page.getByTestId('my-score').fill('85');
    await page.getByTestId('their-score').fill('70');
    await page.getByTestId('submit-result').click();

    await expect(page.getByTestId('result-submitted')).toBeVisible();

    await page.getByTestId('standings-link').click();
    await expect(page).toHaveURL(new RegExp(`/events/${EVENT_SLUG}/standings`));

    const winner = page.locator('[data-testid^="standing-"]').first();
    await expect(winner).toContainText('Ada Lovelace and partner');
    await expect(winner.getByTestId('match-points')).toHaveText('3');
    await expect(winner.getByTestId('victory-points')).toHaveText('85');
});

test('a session that survives a reload keeps the Player where they were', async ({ page }) => {
    await page.goto('/login');
    await page.getByTestId('email').fill(PLAYER.email);
    await page.getByTestId('password').fill(PLAYER.password);
    await page.getByTestId('submit-login').click();

    await expect(page.getByTestId('table-number')).toHaveText(TABLE_NUMBER);

    await page.reload();

    await expect(page.getByTestId('table-number')).toHaveText(TABLE_NUMBER);
    await expect(page).not.toHaveURL(/\/login/);
});
