<?php

namespace Database\Factories;

use App\Enums\ScheduleBlockType;
use App\Models\Event;
use App\Models\EventScheduleBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventScheduleBlock>
 */
class EventScheduleBlockFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('+1 week', '+2 weeks');

        return [
            'event_id' => Event::factory(),
            'label' => fake()->randomElement(['Registration', 'Lunch', 'Awards', 'Evening Social']),
            'type' => ScheduleBlockType::Info,
            'round_id' => null,
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->modify('+1 hour'),
            'display_order' => 0,
        ];
    }

    public function round(int $roundId): self
    {
        return $this->state(fn (): array => [
            'type' => ScheduleBlockType::Round,
            'round_id' => $roundId,
        ]);
    }

    public function paintingVoting(): self
    {
        return $this->state(fn (): array => [
            'type' => ScheduleBlockType::PaintingVoting,
            'label' => 'Painting Voting',
        ]);
    }
}
