<?php

namespace App\Actions\Events;

use App\Models\EventAttendee;
use App\Models\EventInvite;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Take a Player out of an Attendee, and out of the Event with it.
 *
 * Their outstanding Invites for the Event are revoked in the same
 * transaction: an unclaimed Player has no other way in, and a link left
 * working would open an Event, and an Attendee, they are no longer part of.
 * Invites the same Player holds for other Events are untouched.
 */
class RemoveAttendeeMember
{
    public function handle(EventAttendee $attendee, User $member): void
    {
        DB::transaction(function () use ($attendee, $member): void {
            $attendee->members()->detach($member->getKey());

            EventInvite::query()
                ->where('event_id', $attendee->event_id)
                ->where('user_id', $member->getKey())
                ->outstanding()
                ->update(['revoked_at' => now()]);
        });
    }
}
