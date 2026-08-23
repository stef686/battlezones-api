import { fileURLToPath, URL } from 'node:url';

import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import { defineConfig } from 'vitest/config';

export default defineConfig({
    plugins: [vue(), tailwindcss()],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./src', import.meta.url)),
        },
    },
    build: {
        // Every deploy is a fresh upload, so filenames carry the hash and the
        // service worker in #102 can cache them indefinitely.
        sourcemap: false,
    },
    test: {
        environment: 'jsdom',
        include: ['tests/**/*.spec.ts'],
    },
});
