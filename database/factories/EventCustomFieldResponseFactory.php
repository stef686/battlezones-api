<?php

namespace Database\Factories;

use App\Models\EventAttendee;
use App\Models\EventCustomField;
use App\Models\EventCustomFieldResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventCustomFieldResponse>
 */
class EventCustomFieldResponseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_attendee_id' => EventAttendee::factory(),
            'event_custom_field_id' => EventCustomField::factory(),
            'value' => fake()->sentence(),
        ];
    }
}
