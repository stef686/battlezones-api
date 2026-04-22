<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventStanding;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventStanding>
 */
class EventStandingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'event_attendee_id' => EventAttendee::factory(),
            'position' => fake()->numberBetween(1, 100),
        ];
    }
}
