import { describe, expect, it } from 'vitest';

import { readEnvironment } from '@/env';

describe('readEnvironment', () => {
    it('reads the API url from the build environment', () => {
        expect(readEnvironment({ VITE_API_URL: 'https://api.battlezones.app' }).apiUrl)
            .toBe('https://api.battlezones.app');
    });

    it('trims a trailing slash so paths are joined once', () => {
        expect(readEnvironment({ VITE_API_URL: 'https://api.battlezones.app/' }).apiUrl)
            .toBe('https://api.battlezones.app');
    });

    it('refuses to run without an API url rather than guessing one', () => {
        expect(() => readEnvironment({ VITE_API_URL: '' })).toThrow(/VITE_API_URL/);
    });
});
