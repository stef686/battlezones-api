import { execSync } from 'node:child_process';

import { expect, test } from '@playwright/test';

const EVENT_SLUG = 'end-to-end-open';
const INVITE_TOKEN = 'end-to-end-invite-token';
const INVITED_EMAIL = 'invited@battlezones.test';
const NEW_PASSWORD = 'a-password-of-their-own';

/**
 * Reset the world before each run: the flow claims the invited account, which
 * sets a password and revokes the Invite, and the link has to work again.
 */
test.beforeEach(() => {
    execSync('php artisan db:seed --class=EndToEndSeeder --no-interaction', { cwd: '..', stdio: 'pipe' });
});

test('an invited Player enters without a password, is confined to their Event, then claims the account', async ({ page }) => {
    await page.goto(`/invites/${INVITE_TOKEN}`);

    // The invitation describes itself before it asks for anything: the reader
    // has to be able to tell it is genuine.
    await expect(page.getByTestId('invite-event')).toContainText('End To End Open');
    await expect(page.getByTestId('invite-email')).toHaveText(INVITED_EMAIL);
    await expect(page.locator('input[type="password"]')).toHaveCount(0);

    await page.getByTestId('enter-with-invite').click();

    await expect(page).toHaveURL(new RegExp(`/events/${EVENT_SLUG}/my-game`));
    await expect(page.getByTestId('claim-prompt')).toBeVisible();

    // The restriction is one guard, not a check each screen makes: another
    // Event's public standings are still out of reach.
    await page.goto('/events/some-other-event/standings');
    await expect(page).toHaveURL(/\/claim$/);

    await page.getByTestId('claim-password').fill(NEW_PASSWORD);
    await page.getByTestId('claim-password-confirmation').fill(NEW_PASSWORD);
    await page.getByTestId('submit-claim').click();

    await expect(page).toHaveURL(new RegExp(`/events/${EVENT_SLUG}/my-game`));
    await expect(page.getByTestId('claim-prompt')).toHaveCount(0);

    // Claimed, so nothing is confined any more.
    await page.goto('/events/some-other-event/standings');
    await expect(page).toHaveURL(/some-other-event\/standings/);
});

test('a claimed account logs in with the password it just set', async ({ page }) => {
    await page.goto(`/invites/${INVITE_TOKEN}`);
    await page.getByTestId('claim-from-invite').click();

    await page.getByTestId('claim-password').fill(NEW_PASSWORD);
    await page.getByTestId('claim-password-confirmation').fill(NEW_PASSWORD);
    await page.getByTestId('submit-claim').click();

    await expect(page).toHaveURL(new RegExp(`/events/${EVENT_SLUG}/my-game`));

    // A second device: the password is the account's now, not the link's.
    await page.context().clearCookies();
    await page.evaluate(() => window.localStorage.clear());

    await page.goto('/login');
    await page.getByTestId('email').fill(INVITED_EMAIL);
    await page.getByTestId('password').fill(NEW_PASSWORD);
    await page.getByTestId('submit-login').click();

    await expect(page).not.toHaveURL(/\/login/);
});

test('a spent invitation explains itself and points at logging in', async ({ page }) => {
    await page.goto('/invites/a-token-that-was-never-issued');

    await expect(page.getByTestId('invite-dead')).toContainText('not valid');
    await expect(page.getByTestId('invite-login-link')).toBeVisible();
});

test('a forgotten password can be asked for from the login screen', async ({ page }) => {
    await page.goto('/login');
    await page.getByTestId('forgot-password-link').click();

    await expect(page).toHaveURL(/\/forgot-password$/);

    await page.getByTestId('forgot-email').fill(INVITED_EMAIL);
    await page.getByTestId('submit-forgot').click();

    // The answer is the same whether or not the address is on an account.
    await expect(page.getByTestId('reset-link-sent')).toContainText('we have sent a password reset link');
});
