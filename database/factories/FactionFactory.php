<?php

namespace Database\Factories;

use App\Models\Faction;
use App\Models\GameSystem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Faction>
 */
class FactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'game_system_id' => GameSystem::factory(),
            'name' => ucwords($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 100000),
        ];
    }
}
