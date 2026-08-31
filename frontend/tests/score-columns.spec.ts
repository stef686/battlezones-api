import { describe, expect, it } from 'vitest';

import { columnLabel, listedColumns, tableLabel } from '@/api/rounds';
import { columnsOf, type Standing } from '@/api/standings';

function column(slug: string, name: string, isPrimary = false) {
    return { slug, name, is_primary: isPrimary };
}

describe('a score column heading', () => {
    it('takes the initials of a Score Type of several words', () => {
        expect(columnLabel(column('match-points', 'Match Points'))).toBe('MP');
        expect(columnLabel(column('vp', 'Very Victory Points Indeed'))).toBe('VVP');
    });

    it('shortens a Score Type of one word rather than reducing it to a letter', () => {
        expect(columnLabel(column('kills', 'Kills'))).toBe('KIL');
        expect(columnLabel(column('vp', 'VP'))).toBe('VP');
    });
});

describe('where a game is played', () => {
    it('names the table, and says a bye is not at one', () => {
        expect(tableLabel({ is_bye: false, table_number: 5 })).toBe('Table 5');
        expect(tableLabel({ is_bye: true, table_number: null })).toBe('Bye');
    });

    it('says a game has no table yet rather than reading as "Table null"', () => {
        expect(tableLabel({ is_bye: false, table_number: null })).toBe('Table TBC');
    });
});

describe('the columns a listing has room for', () => {
    it('keeps the primary one alone', () => {
        const columns = [column('match-points', 'Match Points'), column('victory-points', 'Victory Points', true)];

        expect(listedColumns(columns)).toEqual([columns[1]]);
    });
});

describe('the columns the standings are scored on', () => {
    function standing(scores: { slug: string; name: string }[]): Standing {
        return {
            id: 1,
            position: 1,
            movement: null,
            attendee: { id: 9, name: 'Sons of Terra', members: [] },
            scores: scores.map((score_type) => ({ value: '1.00', score_type })),
        };
    }

    it('reads them off the standings rather than naming a fixed pair', () => {
        const rows = [standing([{ slug: 'kill-points', name: 'Kill Points' }])];

        expect(columnsOf(rows)).toEqual([{ slug: 'kill-points', name: 'Kill Points' }]);
    });

    it('keeps a column one team has no score under, and lists each of them once', () => {
        const rows = [
            standing([{ slug: 'match-points', name: 'Match Points' }]),
            standing([
                { slug: 'match-points', name: 'Match Points' },
                { slug: 'victory-points', name: 'Victory Points' },
            ]),
        ];

        expect(columnsOf(rows).map((entry) => entry.slug)).toEqual(['match-points', 'victory-points']);
    });

    it('has no columns at all before anybody is ranked', () => {
        expect(columnsOf([])).toEqual([]);
    });
});
