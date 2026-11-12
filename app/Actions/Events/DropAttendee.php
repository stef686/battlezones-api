<?php

namespace App\Actions\Events;

use App\Models\EventAttendee;
use Illuminate\Validation\ValidationException;

/**
 * Mark an Attendee as having left the Event part-way through.
 *
 * From then on pairing leaves it out of every Round generated; everything
 * already played stands — its Games, its scores, its Standing — because its
 * opponents' results depend on them. A Drop touches no Game: an unfinished
 * one is settled through the ordinary correction path, and a Draft Round
 * already pairing the team is left as it is.
 *
 * Offered only once the team has been paired. Before that nothing depends on
 * it, and deleting it is the cleaner tool.
 */
class DropAttendee
{
    /**
     * @throws ValidationException
     */
    public function execute(EventAttendee $attendee): void
    {
        if (! $attendee->games()->exists()) {
            throw ValidationException::withMessages([
                'attendee' => 'This team has not been paired yet, so there is nothing to drop it from. Delete it instead.',
            ]);
        }

        // Conditional on the row not being Dropped already, so two Organisers
        // dropping at once cannot both land and the first time is the one
        // recorded.
        $dropped = EventAttendee::query()
            ->whereKey($attendee->getKey())
            ->whereNull('dropped_at')
            ->update(['dropped_at' => now()]);

        if ($dropped === 0) {
            throw ValidationException::withMessages([
                'attendee' => 'This team has already been dropped.',
            ]);
        }

        $attendee->refresh();
    }
}
