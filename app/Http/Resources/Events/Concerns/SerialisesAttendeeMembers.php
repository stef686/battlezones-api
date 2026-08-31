<?php

namespace App\Http\Resources\Events\Concerns;

use App\Models\EventAttendee;
use App\Models\EventAttendeeMembership;
use Illuminate\Support\Facades\Gate;

trait SerialisesAttendeeMembers
{
    /**
     * The Players competing as this Attendee, each with their own Faction.
     *
     * @return list<array<string, mixed>>
     */
    protected function serialiseMembers(
        EventAttendee $attendee,
        bool $withArmyList = false,
        bool $withClubs = false,
        bool $withInviteState = false,
    ): array {
        // The reader comes from the token rather than the default guard: some
        // of the screens that show army lists hang off endpoints that are
        // public, where the session guard holds nobody and every list would
        // otherwise read as withheld.
        $reader = request()->user('sanctum');

        // Entitlement is a property of the reader and the party, not of each
        // Player, so it is settled once rather than per member.
        $withArmyList = $withArmyList && $reader !== null && Gate::forUser($reader)->allows('viewArmyLists', $attendee);

        // Whether a Player has answered their invitation is the party's own
        // business rather than the field's: it says who is still waiting to be
        // chased, and it is what decides whether a team mate may edit their
        // details for them. It rides on the same permission as amending them.
        $withInviteState = $withInviteState && $reader !== null && Gate::forUser($reader)->allows('update', $attendee);

        return $attendee->memberships
            ->map(function (EventAttendeeMembership $membership) use ($withArmyList, $withClubs, $withInviteState): array {
                $member = [
                    'id' => $membership->user->id,
                    'name' => $membership->user->public_name,
                    'faction' => $membership->faction === null ? null : [
                        'id' => $membership->faction->id,
                        'name' => $membership->faction->name,
                    ],
                    // Whether a list is in, rather than what it says. A Player
                    // needs to know their own submission counts, an Organiser
                    // needs to know which list to reopen, and the field can
                    // already infer it from a team's lists being visible.
                    'army_list_locked' => $membership->isArmyListLocked(),
                ];

                if ($withInviteState) {
                    // The membership rather than the Player, because an
                    // unclaimed account is not addressable by route: the one
                    // Player whose details a team mate may still change is
                    // exactly the one who cannot be named in a URL.
                    $member['membership_id'] = $membership->id;
                    $member['invite_outstanding'] = ! $membership->user->isClaimed();

                    // The address only while it is still the team's to correct.
                    // Until the invitation is answered it is what a team mate
                    // typed and may have mistyped; afterwards it is the
                    // account holder's own, and no team mate's business.
                    if (! $membership->user->isClaimed()) {
                        $member['email'] = $membership->user->email;
                    }
                }

                if ($withArmyList) {
                    $member['army_list'] = $membership->army_list;
                }

                if ($withClubs) {
                    $member['clubs'] = $membership->user->clubs
                        ->map(fn ($club): array => ['id' => $club->id, 'name' => $club->name])
                        ->values()
                        ->all();
                }

                return $member;
            })
            ->values()
            ->all();
    }
}
