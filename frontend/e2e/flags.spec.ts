import { expect, test, type Page } from '@playwright/test';

import { resetWorld } from './reset';

const EVENT_SLUG = 'end-to-end-open';
const PLAYER = { email: 'player@battlezones.test', password: 'end-to-end-password' };
const ORGANISER = { email: 'organiser@battlezones.test', password: 'end-to-end-password' };

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

/**
 * The whole dispute, end to end: a Player says the score is wrong, an
 * Organiser decides it, and the Standings move. Both sides are in one test
 * because it is one conversation — the flag is only interesting to the
 * Organiser, and the correction only interesting back on the Player's screen.
 */
test('a disputed result is flagged, decided by an organiser, and reflected in the standings', async ({ page, browser }) => {
    await logIn(page, PLAYER);
    await page.goto(`/events/${EVENT_SLUG}/my-game`);

    // The result goes in the wrong way round, which is what there is to dispute.
    await page.getByTestId('my-score').fill('70');
    await page.getByTestId('their-score').fill('85');
    await page.getByTestId('submit-result').click();
    await expect(page.getByTestId('result-submitted')).toBeVisible();

    await page.getByTestId('flag-reason').fill('We agreed 85-70 to me.');
    await page.getByTestId('flag-result').click();
    await expect(page.getByTestId('result-flagged')).toContainText('organiser');

    const organiserContext = await browser.newContext();
    const organiser = await organiserContext.newPage();

    await logIn(organiser, ORGANISER);
    await organiser.goto(`/events/${EVENT_SLUG}/organise`);

    await expect(organiser.getByTestId('flags-link')).toContainText('1 waiting');
    await organiser.getByTestId('flags-link').click();

    const queued = organiser.locator('[data-testid^="flag-"]').first();
    await expect(queued).toContainText('We agreed 85-70 to me.');
    await expect(queued).toContainText('Ada Lovelace');

    await organiser.locator('[data-testid^="flag-score-"]').first().fill('85');
    await organiser.locator('[data-testid^="flag-score-"]').nth(1).fill('70');
    await organiser.locator('[data-testid^="correct-flag-"]').first().click();

    // Decided: the queue empties, because a corrected result is not a dispute.
    await expect(organiser.getByTestId('no-flags')).toBeVisible();

    await organiser.goto(`/events/${EVENT_SLUG}/standings`);
    const row = organiser.locator('[data-testid^="standing-"]', { hasText: 'Ada Lovelace and partner' });
    await expect(row.getByTestId('victory-points')).toHaveText('85');

    await organiserContext.close();

    // And the Player is told who changed it, since the change was not theirs.
    await page.goto(`/events/${EVENT_SLUG}/my-game`);
    await expect(page.getByTestId('result-corrected')).toContainText('Rogal Dorn');
    await expect(page.getByTestId('result-flagged')).toHaveCount(0);
});
