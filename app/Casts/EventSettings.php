<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

/**
 * Behavioural settings for an Event.
 *
 * Values live in a single JSON column rather than a column each, but are read
 * and written through typed properties so the pairing and voting code cannot
 * silently disable a rule with a mistyped key.
 */
class EventSettings implements Castable
{
    public function __construct(
        public readonly bool $requiresOpposedAllegiance = false,
        public readonly ?int $roundCount = null,
        public readonly bool $standingsVisible = false,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function fromArray(array $attributes): self
    {
        $unknown = array_diff(array_keys($attributes), array_keys(self::defaults()));

        if ($unknown !== []) {
            throw new InvalidArgumentException(
                'Unknown event setting(s): '.implode(', ', $unknown)
            );
        }

        return new self(
            requiresOpposedAllegiance: (bool) ($attributes['requires_opposed_allegiance'] ?? false),
            roundCount: isset($attributes['round_count']) ? (int) $attributes['round_count'] : null,
            standingsVisible: (bool) ($attributes['standings_visible'] ?? false),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'requires_opposed_allegiance' => $this->requiresOpposedAllegiance,
            'round_count' => $this->roundCount,
            'standings_visible' => $this->standingsVisible,
        ];
    }

    /**
     * Return a copy with the given settings replaced.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function with(array $attributes): self
    {
        return self::fromArray([...$this->toArray(), ...$attributes]);
    }

    /**
     * @return array<string, null>
     */
    private static function defaults(): array
    {
        return [
            'requires_opposed_allegiance' => null,
            'round_count' => null,
            'standings_visible' => null,
        ];
    }

    /**
     * @param  array<int, mixed>  $arguments
     * @return CastsAttributes<self, self>
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes
        {
            /**
             * @param  array<string, mixed>  $attributes
             */
            public function get(mixed $model, string $key, mixed $value, array $attributes): EventSettings
            {
                if ($value === null) {
                    return new EventSettings();
                }

                /** @var array<string, mixed> $decoded */
                $decoded = json_decode((string) $value, true) ?? [];

                return EventSettings::fromArray($decoded);
            }

            /**
             * @param  array<string, mixed>  $attributes
             * @return array<string, string>
             */
            public function set(mixed $model, string $key, mixed $value, array $attributes): array
            {
                if (! $value instanceof EventSettings) {
                    throw new InvalidArgumentException(
                        'Event settings must be assigned as an '.EventSettings::class.' instance.'
                    );
                }

                return [$key => (string) json_encode($value->toArray())];
            }
        };
    }
}
