<?php

namespace App\Http\Resources\Events;

use App\Enums\EventOrganiserRole;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * What the Player reading an Event may see and do there.
 *
 * The SPA asks "am I an Organiser here?" and "which Attendee am I?" on every
 * Event screen. Answering it in the Event response costs nothing extra on
 * venue wifi and stops Organiser-only controls flashing in a round-trip late.
 *
 * Permissions come from the policies rather than a second copy of the rules,
 * so a change to who may register or appoint Organisers cannot leave the
 * screen and the endpoint disagreeing.
 */
class EventViewer
{
    /**
     * Null for an anonymous reader: there is no viewer to describe.
     *
     * @return array<string, mixed>|null
     */
    public static function for(Event $event, ?User $user): ?array
    {
        if (! $user instanceof User) {
            return null;
        }

        $attendee = self::attendeeFor($event, $user);
        $roles = $event->organisers()->whereKey($user->getKey())->pluck('role');

        return [
            'is_organiser' => $roles->isNotEmpty(),
            'is_lead_organiser' => $roles->contains(EventOrganiserRole::Lead->value),
            'is_attendee' => $attendee instanceof EventAttendee,
            'attendee_id' => $attendee?->getKey(),
            'permissions' => [
                'organise' => Gate::forUser($user)->allows('organise', $event),
                'register' => Gate::forUser($user)->allows('register', $event),
                'manage_organisers' => Gate::forUser($user)->allows('manageOrganisers', $event),
            ],
        ];
    }

    /**
     * The party this Player is competing as, in this Event.
     */
    private static function attendeeFor(Event $event, User $user): ?EventAttendee
    {
        return $event->attendees()
            ->whereHas('memberships', fn ($query) => $query->where('user_id', $user->getKey()))
            ->first();
    }
}
