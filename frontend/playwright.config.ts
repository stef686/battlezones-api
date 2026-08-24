import { defineConfig, devices } from '@playwright/test';

/**
 * The browser flow runs against a real API, seeded by EndToEndSeeder, because
 * the point of this test is the seam between the two: auth, CORS, refresh and
 * the result conflict are all things a mocked backend would agree with.
 *
 * The API the SPA talks to is baked in at build time, so `npm run build` must
 * already have run with the right VITE_API_URL — preview only serves what the
 * build produced.
 */

export default defineConfig({
    testDir: './e2e',
    fullyParallel: false,
    workers: 1,
    retries: process.env.CI === undefined ? 0 : 1,
    reporter: process.env.CI === undefined ? 'list' : [['list'], ['html', { open: 'never' }]],
    use: {
        baseURL: 'http://localhost:4173',
        trace: 'on-first-retry',
    },
    projects: [
        { name: 'mobile', use: { ...devices['Pixel 7'] } },
    ],
    webServer: {
        command: 'npm run preview -- --port 4173 --strictPort',
        url: 'http://localhost:4173',
        reuseExistingServer: process.env.CI === undefined,
    },
});
