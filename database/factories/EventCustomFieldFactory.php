<?php

namespace Database\Factories;

use App\Enums\CustomFieldType;
use App\Models\Event;
use App\Models\EventCustomField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventCustomField>
 */
class EventCustomFieldFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => fake()->words(2, true),
            'type' => CustomFieldType::Text,
            'options' => null,
            'display_order' => 0,
        ];
    }
}
