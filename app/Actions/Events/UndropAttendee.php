<?php

namespace App\Actions\Events;

use App\Models\EventAttendee;
use Illuminate\Validation\ValidationException;

/**
 * Reverse a Drop: a mistaken one, or a Player back after lunch.
 *
 * Pairing includes the Attendee again from the next Round generated. Nothing
 * is backfilled for Rounds it sat out — it simply was not in them.
 */
class UndropAttendee
{
    /**
     * @throws ValidationException
     */
    public function execute(EventAttendee $attendee): void
    {
        // Conditional on the row being Dropped, so it is the whole check: an
        // Attendee not Dropped, or undropped a moment ago by someone else,
        // changes no row.
        $undropped = EventAttendee::query()
            ->whereKey($attendee->getKey())
            ->whereNotNull('dropped_at')
            ->update(['dropped_at' => null]);

        if ($undropped === 0) {
            throw ValidationException::withMessages([
                'attendee' => 'This team has not been dropped.',
            ]);
        }

        $attendee->refresh();
    }
}
