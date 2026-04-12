<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventUpdate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventUpdate>
 */
class EventUpdateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'title' => rtrim(fake()->sentence(4), '.'),
            'body' => fake()->paragraphs(2, true),
            'pinned_at' => null,
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function pinned(): self
    {
        return $this->state(fn (): array => ['pinned_at' => now()]);
    }
}
