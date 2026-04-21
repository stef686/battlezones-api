<?php

namespace Database\Factories;

use App\Models\EventScoreType;
use App\Models\EventStanding;
use App\Models\EventStandingScore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventStandingScore>
 */
class EventStandingScoreFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_standing_id' => EventStanding::factory(),
            'event_score_type_id' => EventScoreType::factory(),
            'value' => fake()->randomFloat(2, 0, 100),
        ];
    }
}
