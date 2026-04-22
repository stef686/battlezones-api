<?php

namespace Database\Factories;

use App\Enums\SortDirection;
use App\Models\Event;
use App\Models\EventScoreType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EventScoreType>
 */
class EventScoreTypeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Battle Points', 'VP Difference', 'Sportsmanship', 'Painting Score', 'Best Army']);

        return [
            'event_id' => Event::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'sort_direction' => SortDirection::Desc,
            'display_order' => 0,
        ];
    }
}
