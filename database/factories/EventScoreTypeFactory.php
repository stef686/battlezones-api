<?php

namespace Database\Factories;

use App\Enums\SortDirection;
use App\Models\Event;
use App\Models\EventScoreType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EventScoreType>
 */
class EventScoreTypeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Battle Points', 'VP Difference', 'Sportsmanship', 'Painting Score', 'Best Army']);

        return [
            'event_id' => Event::factory(),
            'name' => $name,
            'abbreviation' => EventScoreType::abbreviate($name),
            'slug' => Str::slug($name),
            'sort_direction' => SortDirection::Desc,
            'is_derived' => false,
            'is_primary' => false,
            'ranking_order' => null,
            'win_points' => null,
            'draw_points' => null,
            'loss_points' => null,
            'display_order' => 0,
        ];
    }

    public function victoryPoints(): static
    {
        return $this->state([
            'name' => 'Victory Points',
            'abbreviation' => 'VP',
            'slug' => 'victory-points',
            'sort_direction' => SortDirection::Desc,
            'is_derived' => false,
        ]);
    }

    /** The one column a Game listing leads with. */
    public function primary(): static
    {
        return $this->state([
            'is_primary' => true,
        ]);
    }

    public function matchPoints(float $win = 3, float $draw = 1, float $loss = 0): static
    {
        return $this->state([
            'name' => 'Match Points',
            'abbreviation' => 'MP',
            'slug' => 'match-points',
            'sort_direction' => SortDirection::Desc,
            'is_derived' => true,
            'win_points' => $win,
            'draw_points' => $draw,
            'loss_points' => $loss,
        ]);
    }

    public function derived(): static
    {
        return $this->state([
            'is_derived' => true,
        ]);
    }

    public function rankedAt(int $order): static
    {
        return $this->state([
            'ranking_order' => $order,
        ]);
    }
}
