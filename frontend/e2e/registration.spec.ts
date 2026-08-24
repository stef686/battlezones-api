import { expect, test, type Page } from '@playwright/test';

import { resetWorld } from './reset';

const EVENT_SLUG = 'end-to-end-open';
const CAPTAIN = { email: 'captain@battlezones.test', password: 'end-to-end-password' };
const PARTNER_EMAIL = 'partner@battlezones.test';

/**
 * Reset the world before each run: the flow enters a team, and a Captain who
 * has already entered is shown their team rather than the entry form.
 */
test.beforeEach(() => {
    resetWorld();
});

/** Log in and wait for the redirect, so the next navigation is not raced. */
async function logIn(page: Page): Promise<void> {
    await page.goto('/login');
    await page.getByTestId('email').fill(CAPTAIN.email);
    await page.getByTestId('password').fill(CAPTAIN.password);
    await page.getByTestId('submit-login').click();

    await expect(page).not.toHaveURL(/\/login/);
}

test('a Captain enters a team, names their partner, and records what they are bringing', async ({ page }) => {
    await logIn(page);

    await page.goto(`/events/${EVENT_SLUG}/register`);

    await expect(page.getByTestId('my-email')).toHaveText(CAPTAIN.email);
    // The Captain's own address is shown, not asked for.
    await expect(page.getByTestId('player-0-email')).toHaveCount(0);

    await page.getByTestId('party-name').fill('The Sigillite and partner');
    await page.getByTestId('allegiance').selectOption('loyalist');
    await page.getByTestId('player-0-faction').selectOption({ label: 'Imperial Fists' });

    await page.getByTestId('player-1-name').fill('Rogal Dorn');
    await page.getByTestId('player-1-email').fill(PARTNER_EMAIL);

    await page.getByTestId('submit-registration').click();

    await expect(page).toHaveURL(new RegExp(`/events/${EVENT_SLUG}/my-team`));
    await expect(page.getByTestId('team-name')).toHaveText('The Sigillite and partner');

    // The partner is on the team from the moment they are named, with their
    // own faction still theirs to choose.
    await expect(page.locator('[data-testid^="team-mate-"]')).toContainText('Rogal Dorn');
    await expect(page.locator('[data-testid^="team-mate-"]')).toContainText('Faction not chosen');

    await expect(page.getByTestId('my-faction')).toHaveValue(/\d+/);
});

test('a Captain amends the team and their own faction after entering', async ({ page }) => {
    await logIn(page);

    await page.goto(`/events/${EVENT_SLUG}/register`);
    await page.getByTestId('allegiance').selectOption('traitor');
    await page.getByTestId('player-1-email').fill(PARTNER_EMAIL);
    await page.getByTestId('submit-registration').click();

    await expect(page).toHaveURL(new RegExp(`/events/${EVENT_SLUG}/my-team`));

    await page.getByTestId('team-name-field').fill('The Sigillite and Dorn');
    await page.getByTestId('my-faction').selectOption({ label: 'Sons of Horus' });
    await page.getByTestId('save-team').click();

    await expect(page.getByTestId('team-saved')).toBeVisible();

    // What was saved is what comes back, not what the form remembered.
    await page.reload();
    await expect(page.getByTestId('team-name')).toHaveText('The Sigillite and Dorn');
    await expect(page.getByTestId('my-faction')).toHaveValue(/\d+/);
});

test('a Captain who has already entered is sent to their team, not the entry form', async ({ page }) => {
    await logIn(page);

    await page.goto(`/events/${EVENT_SLUG}/register`);
    await page.getByTestId('allegiance').selectOption('loyalist');
    await page.getByTestId('player-1-email').fill(PARTNER_EMAIL);
    await page.getByTestId('submit-registration').click();

    await expect(page).toHaveURL(new RegExp(`/events/${EVENT_SLUG}/my-team`));

    await page.goto(`/events/${EVENT_SLUG}/register`);
    await expect(page).toHaveURL(new RegExp(`/events/${EVENT_SLUG}/my-team`));
});
