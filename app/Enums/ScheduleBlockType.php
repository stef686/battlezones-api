<?php

namespace App\Enums;

/**
 * What a schedule block is.
 *
 * A `type` plus a nullable `round_id` rather than a polymorphic target: there
 * are three types and only one of them points at a row. Painting voting is
 * Event-level state — one window — not an entity to reference.
 */
enum ScheduleBlockType: string
{
    case Info = 'info';
    case Round = 'round';
    case PaintingVoting = 'painting_voting';

    public function label(): string
    {
        return match ($this) {
            self::Info => 'Info',
            self::Round => 'Round',
            self::PaintingVoting => 'Painting Voting',
        };
    }

    /**
     * Whether this type points at a Round.
     *
     * Info blocks always render as plain text and never link.
     */
    public function targetsRound(): bool
    {
        return $this === self::Round;
    }
}
