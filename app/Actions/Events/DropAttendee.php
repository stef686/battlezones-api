<?php

namespace App\Actions\Events;

use App\Models\EventAttendee;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Mark an Attendee as having left the Event part-way through.
 *
 * From then on pairing leaves it out of every Round generated; everything
 * already played stands — its Games, its scores, its Standing — because its
 * opponents' results depend on them. A Drop touches no Game: an unfinished
 * one is settled through the ordinary correction path, and a Draft Round
 * already pairing the team is left as it is until a Draft Round can be
 * discarded and generated again (#196). The detail's `paired_in_draft_round`
 * is what lets the Organiser be warned of that before dropping.
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
        // The row is locked for the question and the answer together, so the
        // team cannot be dropped twice and whether it has been paired cannot
        // change in between.
        DB::transaction(function () use ($attendee): void {
            $current = EventAttendee::query()->whereKey($attendee->getKey())->lockForUpdate()->firstOrFail();

            if (! $current->games()->exists()) {
                throw ValidationException::withMessages([
                    'attendee' => 'This team has not been paired yet, so there is nothing to drop it from. Delete it instead.',
                ]);
            }

            if ($current->isDropped()) {
                throw ValidationException::withMessages([
                    'attendee' => 'This team has already been dropped.',
                ]);
            }

            $current->forceFill(['dropped_at' => now()])->save();
        });

        $attendee->refresh();
    }
}
