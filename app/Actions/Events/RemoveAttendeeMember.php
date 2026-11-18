<?php

namespace App\Actions\Events;

use App\Models\EventAttendee;
use App\Models\EventInvite;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Take a Player out of an Attendee.
 *
 * The Invites that brought them into this Attendee are revoked in the same
 * transaction: an unclaimed Player has no other way in, and a link left
 * working would open an Attendee they are no longer part of. Only Invites
 * into this Attendee go: a Captain invitation to enter the Event on their
 * own is not tied to any Attendee and survives, as do the Player's Invites
 * for other Events.
 */
class RemoveAttendeeMember
{
    /**
     * Detach the Player and revoke their outstanding Invites into the Attendee.
     */
    public function execute(EventAttendee $attendee, User $member): void
    {
        DB::transaction(function () use ($attendee, $member): void {
            $attendee->members()->detach($member->getKey());

            EventInvite::query()
                ->where('event_attendee_id', $attendee->getKey())
                ->where('user_id', $member->getKey())
                ->outstanding()
                ->each(fn (EventInvite $invite) => $invite->revoke());
        });
    }
}
