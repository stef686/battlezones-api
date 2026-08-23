<?php

namespace App\Actions\Events;

use App\Exceptions\AttendeeMemberAlreadyEntered;
use App\Models\EventAttendee;
use App\Models\EventAttendeeMembership;
use App\Models\Faction;
use App\Models\User;

/**
 * The single write path for Attendee membership.
 *
 * Every membership row carries a denormalised event_id so that "a User enters
 * an Event once" stays a database guarantee. Routing all writes through here
 * keeps that column consistent rather than relying on each caller to set it.
 */
class AddAttendeeMember
{
    public function handle(
        EventAttendee $attendee,
        User $user,
        ?Faction $faction = null,
        ?string $armyList = null,
    ): EventAttendeeMembership {
        if ($this->alreadyEntered($attendee, $user)) {
            throw AttendeeMemberAlreadyEntered::for($user->name);
        }

        $attendee->members()->attach($user, [
            'event_id' => $attendee->event_id,
            'faction_id' => $faction?->id,
            'army_list' => $armyList,
        ]);

        return EventAttendeeMembership::query()
            ->where('event_attendee_id', $attendee->id)
            ->where('user_id', $user->id)
            ->firstOrFail();
    }

    private function alreadyEntered(EventAttendee $attendee, User $user): bool
    {
        return EventAttendeeMembership::query()
            ->where('event_id', $attendee->event_id)
            ->where('user_id', $user->id)
            ->exists();
    }
}
