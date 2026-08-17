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

    /**
     * The Laravel notification driver this channel sends through.
     *
     * Returns null when no driver is registered for the channel, which lets users
     * keep a preference for a channel the application cannot deliver on yet.
     */
    public function driver(): ?string
    {
        return match ($this) {
            self::Email => 'mail',
            self::Push => null,
        };
    }
}
