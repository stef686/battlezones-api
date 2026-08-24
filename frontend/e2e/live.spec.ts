import { expect, request, test } from '@playwright/test';

import { resetWorld } from './reset';

const API_URL = process.env.VITE_API_URL ?? 'https://battlezones.test';
const EVENT_SLUG = 'end-to-end-open';
const PLAYER = { email: 'player@battlezones.test', password: 'end-to-end-password' };
const ORGANISER = { email: 'organiser@battlezones.test', password: 'end-to-end-password' };
const FIRST_TABLE = '7';
const NEXT_TABLE = '12';

test.beforeEach(() => {
    resetWorld();
});

/**
 * Publish the second Round the way an Organiser will: through the API, from
 * outside the browser under test. The Player's screen must find out on its
 * own — that is the whole point of the exercise.
 */
async function publishTheNextRound(): Promise<void> {
    const api = await request.newContext({ baseURL: API_URL, ignoreHTTPSErrors: true });

    const login = await api.post('/api/login/token', {
        data: { ...ORGANISER, device_name: 'Playwright' },
        headers: { Accept: 'application/json' },
    });
    expect(login.ok()).toBeTruthy();
    const { token } = (await login.json()) as { token: string };

    const headers = { Accept: 'application/json', Authorization: `Bearer ${token}` };

    const rounds = await api.get(`/api/events/${EVENT_SLUG}/rounds`, { headers });
    const draft = ((await rounds.json()) as { data: { id: number; number: number; status: string }[] })
        .data.find((round) => round.status === 'draft');

    expect(draft, 'the seeder leaves a draft round to publish').toBeTruthy();

    const published = await api.post(`/api/events/${EVENT_SLUG}/rounds/${draft!.id}/publish`, { headers });
    expect(published.ok()).toBeTruthy();

    await api.dispose();
}

test('a Player watching their game sees the next round go live without touching anything', async ({ page }) => {
    await page.goto('/login');
    await page.getByTestId('email').fill(PLAYER.email);
    await page.getByTestId('password').fill(PLAYER.password);
    await page.getByTestId('submit-login').click();

    await expect(page.getByTestId('table-number')).toHaveText(FIRST_TABLE);

    await publishTheNextRound();

    // No reload, no navigation, no tap: the pulse notices and the screen
    // follows it. This is story 17.
    await expect(page.getByTestId('table-number')).toHaveText(NEXT_TABLE, { timeout: 45_000 });
});

test('a Player reads the pairings for a live round, with table numbers', async ({ page }) => {
    await page.goto(`/events/${EVENT_SLUG}/rounds`);

    // Only the live Round is listed: the second is still a Draft, and a Player
    // is never sent one.
    await expect(page.locator('[data-testid^="round-"]')).toHaveCount(1);
    await expect(page.getByTestId('draft-badge')).toHaveCount(0);

    await page.locator('[data-testid^="round-"]').first().click();

    await expect(page.getByTestId('round-name')).toContainText('Round 1');
    await expect(page.getByTestId('pairing-table').first()).toHaveText(FIRST_TABLE);
    await expect(page.locator('[data-testid^="pairing-"]').first()).toContainText('Ada Lovelace and partner');
});

test('a draft round is not reachable by guessing its address', async ({ page }) => {
    await publishTheNextRound();

    // Published, then hidden again, so its id is known and it is a Draft.
    const api = await request.newContext({ baseURL: API_URL, ignoreHTTPSErrors: true });
    const login = await api.post('/api/login/token', {
        data: { ...ORGANISER, device_name: 'Playwright' },
        headers: { Accept: 'application/json' },
    });
    const { token } = (await login.json()) as { token: string };
    const headers = { Accept: 'application/json', Authorization: `Bearer ${token}` };

    const rounds = await api.get(`/api/events/${EVENT_SLUG}/rounds`, { headers });
    const second = ((await rounds.json()) as { data: { id: number; number: number }[] })
        .data.find((round) => round.number === 2)!;

    await api.delete(`/api/events/${EVENT_SLUG}/rounds/${second.id}/publish`, { headers });
    await api.dispose();

    await page.goto(`/events/${EVENT_SLUG}/rounds/${second.id}`);

    const notice = page.getByTestId('missing');
    await expect(notice).toBeVisible();
    expect(((await notice.textContent()) ?? '').toLowerCase()).not.toContain('draft');
});
