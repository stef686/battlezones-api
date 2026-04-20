<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Round;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'round_id' => Round::factory(),
            'table_number' => fake()->numberBetween(1, 20),
            'is_bye' => false,
        ];
    }

    public function bye(): self
    {
        return $this->state(fn (): array => ['is_bye' => true]);
    }
}
