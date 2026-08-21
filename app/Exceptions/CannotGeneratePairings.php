<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * The field cannot be paired as it stands.
 *
 * Every case here is something an Organiser can put right — publish the round,
 * chase a table for its result, fix an Attendee's Allegiance — so the message
 * says which, rather than failing as a validation error with no subject.
 */
class CannotGeneratePairings extends RuntimeException
{
    public static function roundNotPublished(int $number): self
    {
        return new self("Round {$number} has not been published yet, so the next Round cannot be paired.");
    }

    public static function resultsOutstanding(int $number): self
    {
        return new self("Round {$number} still has results outstanding, so the next Round cannot be paired.");
    }

    public static function roundCountReached(int $roundCount): self
    {
        return new self("This Event is scheduled for {$roundCount} rounds, which have all been paired.");
    }

    public static function tooFewAttendees(): self
    {
        return new self('There are too few Attendees to pair a Round.');
    }

    public static function allegianceMissing(int $count): self
    {
        return new self("{$count} Attendee(s) have no Allegiance, so this Event's Games cannot be opposed.");
    }

    public static function allegianceOneSided(): self
    {
        return new self('Every Attendee shares one Allegiance, so no Game can be opposed.');
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
