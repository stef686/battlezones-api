<?php

namespace Database\Factories;

use App\Enums\RoundStatus;
use App\Models\Event;
use App\Models\Round;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Round>
 */
class RoundFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'number' => fake()->numberBetween(1, 6),
            'name' => null,
            'status' => RoundStatus::Draft,
        ];
    }

    /**
     * A Round whose Games the Players can see.
     */
    public function live(): self
    {
        return $this->state(fn (): array => ['status' => RoundStatus::Live]);
    }
}
