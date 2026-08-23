<?php

namespace App\Actions\Events;

use App\Enums\EventInviteRole;
use App\Models\EventAttendee;
use App\Models\EventAttendeeMembership;
use App\Models\Faction;
use App\Models\User;

/**
 * Puts one named Player into a party.
 *
 * A Player is identified by email rather than by account, because most of them
 * have no account when their Captain names them. Whoever is doing the enrolling
 * is already here, so they are not sent an invitation to themselves; everybody
 * else is invited, which is what creates the account their membership hangs off.
 */
class EnrolPlayer
{
    public function __construct(
        private readonly AddAttendeeMember $addAttendeeMember,
        private readonly SendEventInvite $sendEventInvite,
    ) {}

    /**
     * @param  array{name?: string|null, email: string, faction_id?: int|null, army_list?: string|null}  $player
     */
    public function handle(EventAttendee $attendee, array $player, User $enrolledBy): EventAttendeeMembership
    {
        return $this->addAttendeeMember->handle(
            $attendee,
            $this->accountFor($attendee, $player, $enrolledBy),
            isset($player['faction_id']) ? Faction::find($player['faction_id']) : null,
            $player['army_list'] ?? null,
        );
    }

    /**
     * @param  array{name?: string|null, email: string, faction_id?: int|null, army_list?: string|null}  $player
     */
    private function accountFor(EventAttendee $attendee, array $player, User $enrolledBy): User
    {
        if (mb_strtolower($player['email']) === mb_strtolower($enrolledBy->email)) {
            return $enrolledBy;
        }

        return $this->sendEventInvite->handle(
            event: $attendee->event,
            email: $player['email'],
            role: EventInviteRole::Player,
            name: $player['name'] ?? null,
            attendee: $attendee,
            invitedBy: $enrolledBy,
        )->user;
    }
}
