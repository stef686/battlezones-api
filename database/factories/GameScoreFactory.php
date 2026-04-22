<?php

namespace Database\Factories;

use App\Models\EventAttendee;
use App\Models\EventScoreType;
use App\Models\Game;
use App\Models\GameScore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameScore>
 */
class GameScoreFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'event_attendee_id' => EventAttendee::factory(),
            'event_score_type_id' => EventScoreType::factory(),
            'value' => fake()->randomFloat(2, 0, 100),
        ];
    }
}
