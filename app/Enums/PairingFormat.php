<?php

namespace App\Enums;

enum PairingFormat: string
{
    case Swiss = 'swiss';
    case RoundRobin = 'round_robin';
    case Knockout = 'knockout';
    case Random = 'random';

    /**
     * Whether Rounds can be paired in this format yet.
     *
     * An Event may only be created in a format that is built (ADR 0006).
     * The rest are offered as coming soon rather than accepted and then
     * quietly paired as Swiss. Change this as each one lands (#167, #168, #169).
     */
    public function isAvailable(): bool
    {
        return match ($this) {
            self::Swiss => true,
            self::RoundRobin, self::Knockout, self::Random => false,
        };
    }

    /**
     * @return list<self>
     */
    public static function available(): array
    {
        return array_values(array_filter(self::cases(), fn (self $format): bool => $format->isAvailable()));
    }
}
