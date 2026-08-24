/**
 * Render the icon sources to the PNG sizes installers and app stores want.
 *
 * Run by hand (`node scripts/build-icons.mjs`) rather than on every build:
 * the PNGs are committed, because a home-screen icon must not depend on a
 * headless browser being available wherever the SPA happens to be built.
 *
 * Chromium comes from Playwright, which the browser tests already need.
 */
import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

import { chromium } from '@playwright/test';

const here = dirname(fileURLToPath(import.meta.url));
const design = resolve(here, '../design');
const out = resolve(here, '../public/icons');

/** Sizes: PWA install, Apple touch icon, and the 1024 a native build needs. */
const targets = [
    { source: 'icon.svg', size: 192, name: 'icon-192.png' },
    { source: 'icon.svg', size: 512, name: 'icon-512.png' },
    { source: 'icon.svg', size: 180, name: 'apple-touch-icon.png' },
    { source: 'icon.svg', size: 1024, name: 'icon-1024.png' },
    { source: 'icon-maskable.svg', size: 512, name: 'icon-maskable-512.png' },
    { source: 'icon-maskable.svg', size: 1024, name: 'icon-maskable-1024.png' },
];

await mkdir(out, { recursive: true });

const browser = await chromium.launch();

for (const target of targets) {
    const svg = await readFile(resolve(design, target.source), 'utf8');
    const page = await browser.newPage({ viewport: { width: target.size, height: target.size } });

    await page.setContent(
        `<style>html,body{margin:0;padding:0}svg{display:block;width:${target.size}px;height:${target.size}px}</style>${svg}`,
    );

    await writeFile(resolve(out, target.name), await page.screenshot({ omitBackground: false }));
    await page.close();
}

await browser.close();

console.log(`Wrote ${targets.length} icons to public/icons.`);
