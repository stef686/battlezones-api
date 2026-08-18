<?php

namespace App\Enums;

enum RegistrationMode: string
{
    case Open = 'open';
    case InviteOnly = 'invite_only';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::InviteOnly => 'Invite Only',
        };
    }
}
