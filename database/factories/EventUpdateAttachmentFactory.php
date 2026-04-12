<?php

namespace Database\Factories;

use App\Models\EventUpdate;
use App\Models\EventUpdateAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventUpdateAttachment>
 */
class EventUpdateAttachmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_update_id' => EventUpdate::factory(),
            'name' => fake()->words(3, true),
            'path' => 'events/updates/'.fake()->uuid().'.pdf',
            'display_order' => 0,
        ];
    }
}
