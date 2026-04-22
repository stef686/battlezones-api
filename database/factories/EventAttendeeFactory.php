<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\Faction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventAttendee>
 */
class EventAttendeeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'faction_id' => Faction::factory(),
            'army_list' => fake()->paragraph(),
            'checked_in_at' => null,
        ];
    }
}
