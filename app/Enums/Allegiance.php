<?php

namespace App\Enums;

/**
 * The side an Attendee fights for where an Event divides the field in two.
 *
 * Declared by the Captain at party level rather than derived from the Players'
 * Factions: a doubles team is one Allegiance with two Factions, and several
 * Horus Heresy factions are playable on either side.
 */
enum Allegiance: string
{
    case Loyalist = 'loyalist';
    case Traitor = 'traitor';

    public function label(): string
    {
        return match ($this) {
            self::Loyalist => 'Loyalist',
            self::Traitor => 'Traitor',
        };
    }
}
