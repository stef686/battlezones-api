<?php

namespace App\Policies;

use App\Enums\RegistrationMode;
use App\Models\Event;
use App\Models\EventInvite;
use App\Models\User;

class EventPolicy
{
    /**
     * Whether this User may create an Event of their own.
     *
     * Claimed accounts only: an unclaimed one expires with the Event it was
     * invited to, and an Event whose Lead expires has nobody who can appoint
     * Organisers. Admins are let through so Filament's create action stays.
     */
    public function create(User $user): bool
    {
        return $user->is_admin || $user->isClaimed();
    }

    /**
     * Whether this Player may run the Event: publish rounds, correct results,
     * open Polls, and read tallies.
     */
    public function organise(User $user, Event $event): bool
    {
        return $event->isOrganisedBy($user);
    }

    /**
     * Whether this Player may enter the Event.
     *
     * Organisers are never shut out: registering a straggler on the day, after
     * entry has closed, is a normal part of running an event.
     */
    public function register(User $user, Event $event): bool
    {
        if ($event->isOrganisedBy($user)) {
            return true;
        }

        if ($event->registrationHasClosed() || $event->isFull()) {
            return false;
        }

        return $event->registration_mode !== RegistrationMode::InviteOnly
            || $this->hasBeenInvited($user, $event);
    }

    /**
     * An invitation is the enrolment right, so it still counts once it has
     * been claimed or has expired; what it stops being is a way to log in.
     */
    private function hasBeenInvited(User $user, Event $event): bool
    {
        return EventInvite::query()
            ->where('event_id', $event->getKey())
            ->where('user_id', $user->getKey())
            ->exists();
    }

    /**
     * Whether this Player may appoint and remove other Organisers.
     */
    public function manageOrganisers(User $user, Event $event): bool
    {
        return $event->isLedBy($user);
    }

    /**
     * Whether this User may take the Event from Draft to Published.
     *
     * The Lead alone: putting an Event in front of Players is the Lead's
     * call, not a co-Organiser's. Admins are let through so that Filament,
     * which checks every ability a policy defines, keeps its repair path.
     */
    public function publish(User $user, Event $event): bool
    {
        return $user->is_admin || $event->isLedBy($user);
    }
}
