import { expect, test } from '@playwright/test';

import { resetWorld } from './reset';

const TOKEN = 'end-to-end-feedback-token';

test.beforeEach(() => {
    resetWorld();
});

/**
 * The whole point of this screen is that it works for somebody who has never
 * signed in on the device the email opened on, so nothing here logs in.
 */
test('a feedback link opens without a session, is answered once, and then says so', async ({ page }) => {
    await page.goto(`/feedback/${TOKEN}`);

    await expect(page.getByTestId('submit-feedback')).toBeVisible();
    await expect(page.locator('main')).toContainText('End To End Open');

    // Thumb-sized targets: this is filled in on a train home, one-handed.
    const rating = page.locator('[data-testid^="rating-"]').first();
    const box = await rating.boundingBox();
    expect(box!.height).toBeGreaterThanOrEqual(44);

    await page.locator('[data-testid^="rating-"]').nth(3).click();
    await page.locator('[data-testid^="answer-"]').first().fill('The missions were excellent.');
    await page.getByTestId('submit-feedback').click();

    await expect(page.getByTestId('feedback-thanks')).toContainText('Thank you');

    // A link works once: coming back to it explains itself rather than showing
    // an empty form that would fail on submit.
    await page.goto(`/feedback/${TOKEN}`);
    await expect(page.getByTestId('feedback-unusable')).toContainText('already been used');
});

test('a link nobody was sent explains itself the same way', async ({ page }) => {
    await page.goto('/feedback/not-a-real-token');

    await expect(page.getByTestId('feedback-unusable')).toBeVisible();

    // Unknown, spent and expired are one answer: which of the three it is
    // would only matter to somebody holding a token they were not sent.
    await expect(page.getByTestId('feedback-unusable')).toContainText('expired');
});
