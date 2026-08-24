import { expect, test, type Page } from '@playwright/test';

import { resetWorld } from './reset';

const EVENT_SLUG = 'end-to-end-open';
const PLAYER = { email: 'player@battlezones.test', password: 'end-to-end-password' };
const OPPONENT = { email: 'opponent@battlezones.test', password: 'end-to-end-password' };
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

/** The opposing team's page, reached the way an opponent reaches it. */
async function openTheirTeam(page: Page): Promise<void> {
    await page.goto(`/events/${EVENT_SLUG}/attendees`);
    await page.getByText('Ada Lovelace and partner').click();
    await expect(page.getByTestId('attendee-name')).toContainText('Ada Lovelace');
}

test('a list is submitted, locked, read by an opponent, and reopened by an organiser', async ({ page, browser }) => {
    await logIn(page, PLAYER);
    await page.goto(`/events/${EVENT_SLUG}/my-team`);

    await page.getByTestId('army-list').fill('Legion Tactical Squad, 10 models, with a Rhino.');
    await page.getByTestId('submit-army-list').click();

    // Locked is said, not implied: there is nothing left to type into.
    await expect(page.getByTestId('army-list-locked')).toContainText('organiser');
    await expect(page.getByTestId('army-list')).toHaveCount(0);

    const opponentContext = await browser.newContext();
    const opponent = await opponentContext.newPage();

    await logIn(opponent, OPPONENT);
    await openTheirTeam(opponent);

    await expect(opponent.locator('[data-testid^="army-list-"]').first()).toContainText('with a Rhino');

    // An Organiser reopens it, which is the only way back out of locked.
    const organiserContext = await browser.newContext();
    const organiser = await organiserContext.newPage();

    await logIn(organiser, ORGANISER);
    await openTheirTeam(organiser);
    await organiser.locator('[data-testid^="unlock-"]').first().click();
    await expect(organiser.getByTestId('lists-not-revealed')).toBeVisible();

    // Reopened means unsubmitted: the field cannot read it again until it is.
    await opponent.reload();
    await expect(opponent.getByTestId('lists-not-revealed')).toBeVisible();
    await expect(opponent.locator('[data-testid^="army-list-"]')).toHaveCount(0);

    // And the Player has their list back, with what they wrote still in it.
    await page.reload();
    await expect(page.getByTestId('army-list')).toHaveValue(/Rhino/);

    await opponentContext.close();
    await organiserContext.close();
});
