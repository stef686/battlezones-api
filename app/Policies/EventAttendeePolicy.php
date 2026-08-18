<?php

namespace App\Policies;

use App\Models\EventAttendee;
use App\Models\User;

class EventAttendeePolicy
{
    /**
     * Whether this Player may amend the party's own details.
     *
     * Its own members and the Event's Organisers; nobody else touches a team
     * they are not part of and do not run.
     */
    public function update(User $user, EventAttendee $attendee): bool
    {
        // Platform admins repair teams through Filament, which is the way out
        // of anything the API deliberately freezes.
        return $user->is_admin
            || $attendee->hasMember($user)
            || $attendee->event->isOrganisedBy($user);
    }

    /**
     * Whether this Player may change who is in the party.
     *
     * The registration deadline gates membership as it gates entry, since a
     * swap after the deadline is a late entry by another name. Organisers are
     * never blocked: repairing a broken team on the day is their job.
     */
    public function changeMembers(User $user, EventAttendee $attendee): bool
    {
        if ($user->is_admin || $attendee->event->isOrganisedBy($user)) {
            return true;
        }

        return $attendee->hasMember($user) && ! $attendee->event->registrationHasClosed();
    }
}
