import { expect, test } from '@playwright/test';

import { resetWorld } from './reset';

const EVENT_SLUG = 'end-to-end-open';

test.beforeEach(() => {
    resetWorld();
});

test('anyone can read the event, its schedule, and who is in the room', async ({ page }) => {
    await page.goto(`/events/${EVENT_SLUG}`);

    await expect(page.getByTestId('event-name')).toHaveText('End To End Open');
    await expect(page.getByTestId('venue-line').first()).toHaveText('The Test Hall');

    await page.getByTestId('schedule-link').click();
    await expect(page).toHaveURL(new RegExp(`/events/${EVENT_SLUG}/schedule`));

    // Written to the database out of order, rendered in time order.
    await expect(page.getByTestId('block-time')).toHaveText(['08:30', '09:30']);

    await page.getByTestId('event-nav-attendees').click();
    await expect(page).toHaveURL(new RegExp(`/events/${EVENT_SLUG}/attendees`));

    const loyalist = page.locator('[data-testid^="attendee-"]', { hasText: 'Ada Lovelace' });
    await expect(loyalist).toContainText('Loyalist');

    await loyalist.click();
    await expect(page.getByTestId('attendee-name')).toContainText('Ada Lovelace');
    await expect(page.locator('[data-testid^="member-"]').first()).toContainText('Imperial Fists');
});

test('the two sides are told apart by more than colour, and by colour too', async ({ page }) => {
    await page.goto(`/events/${EVENT_SLUG}/attendees`);

    const loyalist = page.getByTestId('allegiance-loyalist').first();
    const traitor = page.getByTestId('allegiance-traitor').first();

    await expect(loyalist).toHaveText('Loyalist');
    await expect(traitor).toHaveText('Traitor');

    // Far enough apart to read across a dim hall at a glance.
    const loyalistColour = await loyalist.evaluate((node) => getComputedStyle(node).backgroundColor);
    const traitorColour = await traitor.evaluate((node) => getComputedStyle(node).backgroundColor);
    expect(loyalistColour).not.toBe(traitorColour);
});

test('an event nobody may see reads exactly like one that does not exist', async ({ page }) => {
    await page.goto('/events/no-such-event-was-ever-run');

    const notice = page.getByTestId('missing');
    await expect(notice).toBeVisible();

    const wording = (await notice.textContent())?.toLowerCase() ?? '';
    expect(wording).toContain('not found');
    expect(wording).not.toContain('private');
    expect(wording).not.toContain('permission');
});

test('a reader with no account sees no organiser controls', async ({ page }) => {
    await page.goto(`/events/${EVENT_SLUG}`);

    await expect(page.getByTestId('event-name')).toBeVisible();
    await expect(page.getByTestId('organiser-controls')).toHaveCount(0);
});
