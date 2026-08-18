<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Whether this Player may run the Event: publish rounds, correct results,
     * open Polls, and read tallies.
     */
    public function organise(User $user, Event $event): bool
    {
        return $event->isOrganisedBy($user);
    }

    /**
     * Whether this Player may appoint and remove other Organisers.
     */
    public function manageOrganisers(User $user, Event $event): bool
    {
        return $event->isLedBy($user);
    }
}
