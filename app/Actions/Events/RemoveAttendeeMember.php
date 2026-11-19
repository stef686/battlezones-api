<?php

namespace App\Actions\Events;

use App\Models\EventAttendee;
use App\Models\EventInvite;
use App\Models\User;
use App\Notifications\Events\RemovedFromEventNotification;
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
 *
 * While the Event is Published or Active the removed Player is told, unless
 * they removed themselves, once the removal has committed: a removal that
 * rolls back tells nobody anything.
 */
class RemoveAttendeeMember
{
    /**
     * Detach the Player and revoke their outstanding Invites into the Attendee.
     */
    public function execute(EventAttendee $attendee, User $member, User $removedBy): void
    {
        DB::transaction(function () use ($attendee, $member, $removedBy): void {
            $attendee->members()->detach($member->getKey());

            EventInvite::query()
                ->where('event_attendee_id', $attendee->getKey())
                ->where('user_id', $member->getKey())
                ->outstanding()
                ->each(fn (EventInvite $invite) => $invite->revoke());

            if ($member->isNot($removedBy) && $attendee->event->status->announcesRemovals()) {
                // Read once the Player is gone, so an unnamed team is called
                // after who is left in it rather than after them.
                $attendeeName = $attendee->load('memberships.user')->displayName();
                $notification = RemovedFromEventNotification::memberRemoved($attendee->event, $removedBy, $attendeeName);

                DB::afterCommit(fn () => $member->notify($notification));
            }
        });
    }
}
