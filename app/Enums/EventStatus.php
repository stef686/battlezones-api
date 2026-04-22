<?php

namespace App\Enums;

enum EventStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function isPubliclyVisible(): bool
    {
        return match ($this) {
            self::Published, self::Active, self::Completed => true,
            self::Draft, self::Cancelled => false,
        };
    }

    public function hasRoundsVisible(): bool
    {
        return match ($this) {
            self::Active, self::Completed => true,
            self::Draft, self::Published, self::Cancelled => false,
        };
    }
}
