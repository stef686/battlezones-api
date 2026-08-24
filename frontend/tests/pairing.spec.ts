import { describe, expect, it } from 'vitest';

import type { Pairing } from '@/api/rounds';
import { isOpposed, previewSwap } from '@/lib/pairing';

function attendee(id: number, name: string, allegiance: string | null) {
    return { id, name, allegiance, members: [], scores: {} };
}

function game(id: number, table: number | null, attendees: ReturnType<typeof attendee>[], isBye = false): Pairing {
    return {
        id,
        table_number: table,
        is_bye: isBye,
        is_rematch: false,
        result: { submitted_at: null, is_flagged: false },
        attendees,
    };
}

const L1 = attendee(1, 'Loyal One', 'loyalist');
const T1 = attendee(2, 'Traitor One', 'traitor');
const L2 = attendee(3, 'Loyal Two', 'loyalist');
const T2 = attendee(4, 'Traitor Two', 'traitor');

describe('previewing a swap', () => {
    it('exchanges the matching side, leaving both games opposed', () => {
        const first = game(10, 1, [L1, T1]);
        const second = game(11, 2, [L2, T2]);

        const preview = previewSwap(first, second, true);

        expect(preview.ok).toBe(true);
        if (!preview.ok) {
            return;
        }

        // Each table keeps the Attendee already sitting at it; only the
        // opponent changes. That is what the API does, so it is what the
        // Organiser is shown.
        expect(preview.games[0].attendees.map((a) => a.id)).toEqual([L1.id, T2.id]);
        expect(preview.games[1].attendees.map((a) => a.id)).toEqual([L2.id, T1.id]);
        expect(preview.opposed).toBe(true);
    });

    it('keeps each game\'s table number, so printed sheets stay right', () => {
        const preview = previewSwap(game(10, 1, [L1, T1]), game(11, 2, [L2, T2]), true);

        expect(preview.ok && preview.games.map((g) => g.table_number)).toEqual([1, 2]);
    });

    it('says when the swap would leave a game unopposed', () => {
        // Nobody declared a side, so exchanging the second Attendee pairs like
        // against like — which the Organiser should see before committing.
        const first = game(10, 1, [attendee(1, 'One', 'loyalist'), attendee(2, 'Two', 'loyalist')]);
        const second = game(11, 2, [attendee(3, 'Three', 'loyalist'), attendee(4, 'Four', 'loyalist')]);

        const preview = previewSwap(first, second, false);

        expect(preview.ok).toBe(true);
        expect(preview.ok && preview.opposed).toBe(false);
    });

    it('refuses to swap a game with itself', () => {
        const only = game(10, 1, [L1, T1]);

        expect(previewSwap(only, only, true)).toEqual({
            ok: false,
            reason: 'A game has to be swapped with a different game.',
        });
    });

    it('refuses two byes, which have nothing to exchange', () => {
        const preview = previewSwap(game(20, null, [L1], true), game(21, null, [L2], true), true);

        expect(preview.ok).toBe(false);
        expect(preview.ok === false && preview.reason).toContain('nothing to exchange');
    });

    it('says so when neither game has a side on the same allegiance', () => {
        const first = game(10, 1, [L1, T1]);
        const second = game(11, 2, [attendee(5, 'Five', 'loyalist'), attendee(6, 'Six', 'loyalist')]);

        // The moving side is a Traitor and the other Game has none.
        expect(previewSwap(first, second, true).ok).toBe(false);
    });
});

describe('previewing a bye being moved', () => {
    it('puts the bye Attendee in the game and gives the Bye to the one they displace', () => {
        const bye = game(20, null, [L2], true);
        const table = game(10, 1, [L1, T1]);

        const preview = previewSwap(bye, table, true);

        expect(preview.ok).toBe(true);
        if (!preview.ok) {
            return;
        }

        const [byeAfter, gameAfter] = preview.games;

        // The displaced Attendee is the one on the bye Attendee's own side, so
        // the Game stays opposed.
        expect(byeAfter.attendees.map((a) => a.id)).toEqual([L1.id]);
        expect(gameAfter.attendees.map((a) => a.id)).toEqual([L2.id, T1.id]);
        expect(preview.opposed).toBe(true);
    });

    it('works whichever way round the two games were chosen', () => {
        const bye = game(20, null, [L2], true);
        const table = game(10, 1, [L1, T1]);

        expect(previewSwap(table, bye, true)).toEqual(previewSwap(bye, table, true));
    });
});

describe('whether a game is opposed', () => {
    it('counts a bye as opposed, since it has nobody to oppose', () => {
        expect(isOpposed(game(20, null, [L1], true))).toBe(true);
    });

    it('counts two undeclared sides as not opposed', () => {
        expect(isOpposed(game(10, 1, [attendee(1, 'One', null), attendee(2, 'Two', null)]))).toBe(false);
    });

    it('counts two of the same side as not opposed', () => {
        expect(isOpposed(game(10, 1, [L1, L2]))).toBe(false);
    });
});
