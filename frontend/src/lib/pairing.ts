import type { Pairing } from '@/api/rounds';

/**
 * What a swap would produce, worked out before it is committed.
 *
 * This mirrors `SwapRoundPairings`: each Game keeps its first Attendee and
 * exchanges the second, choosing the side that shares an Allegiance where the
 * Event opposes them. The API sends Attendees in pairing order for exactly
 * this reason.
 *
 * It is a preview, not a decision. The server performs the swap and refuses
 * anything this got wrong — including the one rule that cannot be checked
 * from a single Round, that a Bye must stay with the larger Allegiance.
 */
export type SwapPreview =
    | { ok: true; games: [Pairing, Pairing]; opposed: boolean }
    | { ok: false; reason: string };

export function previewSwap(first: Pairing, second: Pairing, opposesAllegiances: boolean): SwapPreview {
    if (first.id === second.id) {
        return { ok: false, reason: 'A game has to be swapped with a different game.' };
    }

    if (first.is_bye && second.is_bye) {
        return { ok: false, reason: 'Two byes have nothing to exchange.' };
    }

    if (first.is_bye || second.is_bye) {
        return previewByeMove(first.is_bye ? first : second, first.is_bye ? second : first, opposesAllegiances);
    }

    const moving = first.attendees[1];

    if (moving === undefined) {
        return { ok: false, reason: 'These games do not have a matching side to exchange.' };
    }

    const match = opposesAllegiances
        ? second.attendees.find((attendee) => attendee.allegiance === moving.allegiance)
        : second.attendees[1];

    if (match === undefined) {
        return { ok: false, reason: 'These games do not have a matching side to exchange.' };
    }

    const firstAfter = replace(first, moving.id, match);
    const secondAfter = replace(second, match.id, moving);

    return {
        ok: true,
        games: [firstAfter, secondAfter],
        opposed: isOpposed(firstAfter) && isOpposed(secondAfter),
    };
}

/**
 * The bye Attendee joins the Game, and the one they displace takes the Bye.
 */
function previewByeMove(bye: Pairing, game: Pairing, opposesAllegiances: boolean): SwapPreview {
    const byeAttendee = bye.attendees[0];

    if (byeAttendee === undefined) {
        return { ok: false, reason: 'These games do not have a matching side to exchange.' };
    }

    const displaced = opposesAllegiances
        ? game.attendees.find((attendee) => attendee.allegiance === byeAttendee.allegiance)
        : game.attendees[game.attendees.length - 1];

    if (displaced === undefined) {
        return { ok: false, reason: 'These games do not have a matching side to exchange.' };
    }

    const gameAfter = replace(game, displaced.id, byeAttendee);
    const byeAfter = { ...bye, attendees: [displaced] };

    return {
        ok: true,
        games: [byeAfter, gameAfter],
        opposed: isOpposed(gameAfter),
    };
}

/** A Game is opposed when its two sides are on declared, different Allegiances. */
export function isOpposed(pairing: Pairing): boolean {
    if (pairing.is_bye || pairing.attendees.length < 2) {
        return true;
    }

    const [first, second] = pairing.attendees;

    return first?.allegiance != null && first.allegiance !== second?.allegiance;
}

function replace(pairing: Pairing, leavingId: number, arriving: Pairing['attendees'][number]): Pairing {
    return {
        ...pairing,
        attendees: pairing.attendees.map((attendee) => (attendee.id === leavingId ? arriving : attendee)),
    };
}
