import { fileURLToPath, URL } from 'node:url';

import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import { defineConfig } from 'vitest/config';
import { VitePWA } from 'vite-plugin-pwa';

/** The dark surface the app paints, so the shell matches before Vue boots. */
const THEME_COLOUR = '#0a0909';

export default defineConfig({
    plugins: [
        vue(),
        tailwindcss(),
        VitePWA({
            // Prompt, never auto: a Player mid-result-submission must not have
            // the page swapped under them. The banner is the reader's choice,
            // and taking it reloads once.
            registerType: 'prompt',
            includeAssets: ['icons/apple-touch-icon.png'],
            manifest: {
                name: 'Battlezones',
                short_name: 'Battlezones',
                description: 'Pairings, results and standings for tabletop events.',
                start_url: '/',
                scope: '/',
                display: 'standalone',
                orientation: 'portrait',
                background_color: THEME_COLOUR,
                theme_color: THEME_COLOUR,
                icons: [
                    { src: '/icons/icon-192.png', sizes: '192x192', type: 'image/png' },
                    { src: '/icons/icon-512.png', sizes: '512x512', type: 'image/png' },
                    { src: '/icons/icon-maskable-512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
                ],
            },
            workbox: {
                // The app shell only. Hashed filenames mean a precached asset
                // is never the wrong one, and a new deploy brings new names.
                globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}'],
                // Deliberately empty, and it must stay empty: server state is
                // cached by the query layer, and a second cache that disagrees
                // with it produces stale standings nobody can debug at a venue.
                runtimeCaching: [],
                // A deep link into the SPA is served the shell; anything under
                // /api is a request to the API and never a navigation.
                navigateFallback: '/index.html',
                navigateFallbackDenylist: [/^\/api\//],
                // No stale window keeps serving the previous bundle once the
                // reader has taken the prompt.
                cleanupOutdatedCaches: true,
                clientsClaim: true,
            },
            devOptions: {
                // Off in dev: a service worker in front of the Vite dev server
                // is a debugging trap, and there is nothing to update there.
                enabled: false,
            },
        }),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./src', import.meta.url)),
        },
    },
    build: {
        // Every deploy is a fresh upload, so filenames carry the hash and the
        // service worker caches them indefinitely.
        sourcemap: false,
    },
    test: {
        environment: 'jsdom',
        include: ['tests/**/*.spec.ts'],
    },
});
