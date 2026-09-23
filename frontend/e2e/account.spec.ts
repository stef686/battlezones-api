import { expect, test } from '@playwright/test';

import { resetWorld } from './reset';

const PLAYER = { email: 'player@battlezones.test', password: 'end-to-end-password' };

test.beforeEach(() => {
    resetWorld();
});

test('a Player opens the account drawer from their avatar and logs out', async ({ page }) => {
    await page.goto('/login');
    await page.getByTestId('email').fill(PLAYER.email);
    await page.getByTestId('password').fill(PLAYER.password);
    await page.getByTestId('submit-login').click();
    await expect(page).not.toHaveURL(/\/login/);

    await page.getByTestId('tab-account').click();

    const drawer = page.getByTestId('account-drawer');
    await expect(drawer).toBeVisible();
    await expect(drawer.getByTestId('account-drawer-name')).toHaveText('Ada Lovelace');

    // Opening focus goes to Close, never to Log out.
    await expect(drawer.getByTestId('account-drawer-close')).toBeFocused();

    await page.keyboard.press('Escape');
    await expect(drawer).toBeHidden();

    await page.getByTestId('tab-account').click();
    await drawer.getByTestId('account-drawer-logout').click();

    await expect(page).toHaveURL(/\/login$/);
    await expect(page.getByTestId('tab-account')).toHaveAttribute('href', '/login');

    // The token is gone, not just hidden: a reload stays signed out.
    await page.reload();
    await expect(page.getByTestId('tab-account')).toHaveAttribute('href', '/login');
});
