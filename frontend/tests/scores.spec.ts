import { describe, expect, it } from 'vitest';

import { formatScore } from '@/lib/scores';

describe('a score on screen', () => {
    it('drops the decimals a whole number does not need', () => {
        expect(formatScore('3.00')).toBe('3');
        expect(formatScore('85.00')).toBe('85');
        expect(formatScore(0)).toBe('0');
    });

    it('keeps the places a half point actually uses', () => {
        expect(formatScore('3.50')).toBe('3.5');
        expect(formatScore('0.25')).toBe('0.25');
    });

    it('hands back anything that is not a number untouched, rather than NaN', () => {
        expect(formatScore('withdrawn')).toBe('withdrawn');
    });
});
