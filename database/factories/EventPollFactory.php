<?php

namespace Database\Factories;

use App\Enums\PollType;
use App\Models\Event;
use App\Models\EventPoll;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventPoll>
 */
class EventPollFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => 'Best Painted Army',
            'type' => PollType::Painting,
            'opens_at' => null,
            'closes_at' => null,
            'votes_per_player' => 1,
        ];
    }

    public function open(): self
    {
        return $this->state(fn (): array => [
            'opens_at' => now()->subHour(),
            'closes_at' => null,
        ]);
    }

    public function closed(): self
    {
        return $this->state(fn (): array => [
            'opens_at' => now()->subHours(2),
            'closes_at' => now()->subHour(),
        ]);
    }

    public function favouriteOpponent(): self
    {
        return $this->state(fn (): array => [
            'name' => 'Favourite Opponent',
            'type' => PollType::FavouriteOpponent,
        ]);
    }
}
