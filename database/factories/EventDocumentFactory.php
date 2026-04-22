<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventDocument>
 */
class EventDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => fake()->words(3, true),
            'path' => 'events/documents/'.fake()->uuid().'.pdf',
        ];
    }
}
