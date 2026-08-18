<?php

namespace App\Enums;

/**
 * What an Invite entitles its recipient to do once they follow the link.
 *
 * Both roles share one mechanism, one token format and one expiry; they differ
 * only in this value and in whether an Attendee is pre-set.
 */
enum EventInviteRole: string
{
    /** Invited by an Organiser to register an Attendee of their own. */
    case Captain = 'captain';

    /** Invited by a Captain to join an Attendee that already exists. */
    case Player = 'player';

    public function label(): string
    {
        return match ($this) {
            self::Captain => 'Captain',
            self::Player => 'Player',
        };
    }
}
