<?php

namespace App\Enums;

enum EventOrganiserRole: string
{
    case Lead = 'lead';
    case Organiser = 'organiser';

    public function label(): string
    {
        return match ($this) {
            self::Lead => 'Lead Organiser',
            self::Organiser => 'Organiser',
        };
    }

    /**
     * Whether this role may appoint and remove other Organisers.
     *
     * Only the lead may, so an appointed Organiser cannot remove the person who
     * appointed them and lock everyone out mid-event.
     */
    public function canManageOrganisers(): bool
    {
        return $this === self::Lead;
    }
}
