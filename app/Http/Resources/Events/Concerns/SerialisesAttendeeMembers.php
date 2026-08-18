<?php

namespace App\Http\Resources\Events\Concerns;

use App\Models\EventAttendee;
use App\Models\EventAttendeeMembership;

trait SerialisesAttendeeMembers
{
    /**
     * The Players competing as this Attendee, each with their own Faction.
     *
     * @return list<array<string, mixed>>
     */
    protected function serialiseMembers(EventAttendee $attendee, bool $withArmyList = false, bool $withClubs = false): array
    {
        return $attendee->memberships
            ->map(function (EventAttendeeMembership $membership) use ($withArmyList, $withClubs): array {
                $member = [
                    'id' => $membership->user->id,
                    'name' => $membership->user->public_name,
                    'faction' => $membership->faction === null ? null : [
                        'id' => $membership->faction->id,
                        'name' => $membership->faction->name,
                    ],
                ];

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
