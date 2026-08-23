<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * The two Games cannot be recombined into a legal Round.
 *
 * A swap is an exchange of the same-allegiance side between two Games. Every
 * other shape either produces a Game that is not opposed or a Bye the field
 * cannot be paired around, so it is refused with the reason rather than
 * silently corrected.
 */
class CannotSwapPairing extends RuntimeException
{
    public static function roundNotDraft(): self
    {
        return new self('This Round is live, so its pairings can no longer be swapped.');
    }

    public static function sameGame(): self
    {
        return new self('A Game has to be swapped with a different Game.');
    }

    public static function gameNotInRound(): self
    {
        return new self('Both Games must belong to this Round.');
    }

    public static function bothByes(): self
    {
        return new self('Two Byes have nothing to exchange.');
    }

    public static function noOpposedSide(): self
    {
        return new self('These Games do not have a matching side to exchange.');
    }

    public static function byeWouldLeaveMajority(): self
    {
        return new self('A Bye has to stay with the Allegiance that has more Attendees, or the Round cannot be paired.');
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
