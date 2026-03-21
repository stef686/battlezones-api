<?php

namespace App\Enums;

enum NotificationChannel: string
{
    case Email = 'email';
    case Push = 'push';

    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::Push => 'Push',
        };
    }
}
