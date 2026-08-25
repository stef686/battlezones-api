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
 * One window, both sides of it: an Organiser opens the vote, a Player finds it
 * without going looking, votes, and the tallies only exist once it is shut.
 */
test('a vote is opened, cast in, closed, and counted', async ({ page, browser }) => {
    const organiserContext = await browser.newContext();
    const organiser = await organiserContext.newPage();

    await logIn(organiser, ORGANISER);
    await organiser.goto(`/events/${EVENT_SLUG}/polls`);

    // Nothing to count while people are still voting, so nothing is offered.
    await expect(organiser.locator('[data-testid^="show-results-"]')).toHaveCount(0);
    await organiser.locator('[data-testid^="open-poll-"]').first().click();
    await expect(organiser.locator('[data-testid^="close-poll-"]').first()).toBeVisible();

    await logIn(page, PLAYER);
    await page.goto(`/events/${EVENT_SLUG}`);

    // Found on the screen they were already on, not hunted for.
    await expect(page.getByTestId('voting-open')).toContainText('Best Painted Army');
    await page.getByTestId('voting-open').click();

    await expect(page.getByTestId('picks-left')).toContainText('2 of 2');

    const candidates = page.locator('[data-testid^="pick-"]');
    await candidates.first().click();
    await expect(page.getByTestId('picks-left')).toContainText('1 of 2');

    await page.getByTestId('save-ballot').click();
    await expect(page.getByTestId('ballot-saved')).toBeVisible();

    // Their ballot comes back with them, because revising it is the same act.
    await page.reload();
    await expect(page.locator('[data-testid^="pick-"]').first()).toHaveClass(/border-primary/);

    // A Player enters their own army so it can be voted for in turn.
    await page.goto(`/events/${EVENT_SLUG}/my-team`);
    await page.getByTestId('enter-painting').click();
    await expect(page.getByTestId('painting-entered')).toContainText('display table');

    await organiser.reload();
    await organiser.locator('[data-testid^="close-poll-"]').first().click();
    await organiser.locator('[data-testid^="show-results-"]').first().click();

    const tallies = organiser.locator('[data-testid^="results-"]').first();
    await expect(tallies).toContainText('1');

    // And voting is over for the Player, who is told so rather than left with
    // buttons that no longer work.
    await page.goto(`/events/${EVENT_SLUG}/polls`);
    await expect(page.locator('[data-testid^="poll-"]').first()).toContainText('Closed');

    await organiserContext.close();
});
