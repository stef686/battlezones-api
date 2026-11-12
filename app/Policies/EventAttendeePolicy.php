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
     * Whether this User may delete the Attendee outright.
     *
     * Its Organisers only: a team cannot withdraw itself by deleting, since
     * a delete takes Invites and ballots with it and cannot be undone.
     * Admins too, or Filament's Delete action disappears.
     */
    public function delete(User $user, EventAttendee $attendee): bool
    {
        return $user->is_admin || $attendee->event->isOrganisedBy($user);
    }

    /**
     * Whether this User may mark the Attendee as having left the Event, or
     * take that back.
     *
     * Its Organisers only: a Player cannot drop themselves (see Drop in
     * CONTEXT.md). Admins too, or Filament loses the action.
     */
    public function drop(User $user, EventAttendee $attendee): bool
    {
        return $user->is_admin || $attendee->event->isOrganisedBy($user);
    }

    /**
     * Whether this Player may read the party's army lists.
     *
     * Lists are competitive information, so reading one costs attendance at
     * the Event: everything else on an Attendee stays public, but a list that
     * anyone could scrape would be a list nobody submits honestly. A team
     * always sees its own.
     */
    public function viewArmyLists(User $user, EventAttendee $attendee): bool
    {
        if ($attendee->hasMember($user)) {
            return true;
        }

        if (! $attendee->armyListsAreVisible()) {
            return false;
        }

        return $user->is_admin
            || $attendee->event->isOrganisedBy($user)
            || $attendee->event->isAttendedBy($user);
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
