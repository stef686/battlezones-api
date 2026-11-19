<?php

namespace App\Enums;

enum EventStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * Whether a Player who loses their place in the Event is told.
     *
     * Only while it is open or under way: once Completed or Cancelled,
     * everyone has already heard, or the Player never turned up.
     */
    public function announcesRemovals(): bool
    {
        return in_array($this, [self::Published, self::Active], true);
    }

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
