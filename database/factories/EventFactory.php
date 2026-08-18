<?php

namespace Database\Factories;

use App\Casts\EventSettings;
use App\Enums\Country;
use App\Enums\EventStatus;
use App\Enums\PairingFormat;
use App\Models\Event;
use App\Models\GameSystem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->sentence(3);
        $starts = fake()->dateTimeBetween('+1 week', '+6 months');
        $ends = (clone $starts)->modify('+2 days');

        return [
            'game_system_id' => GameSystem::factory(),
            'club_id' => null,
            'name' => rtrim($name, '.'),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 100000),
            'description' => fake()->paragraph(),
            'status' => EventStatus::Draft,
            'pairing_format' => PairingFormat::Swiss,
            'starts_at' => $starts,
            'ends_at' => $ends,
            'venue_name' => fake()->company().' Hall',
            'venue_address' => fake()->streetAddress(),
            'venue_city' => fake()->city(),
            'venue_country' => fake()->randomElement(Country::cases())->value,
            'max_attendees' => fake()->numberBetween(16, 128),
        ];
    }

    public function draft(): self
    {
        return $this->state(fn (): array => ['status' => EventStatus::Draft]);
    }

    public function published(): self
    {
        return $this->state(fn (): array => ['status' => EventStatus::Published]);
    }

    public function active(): self
    {
        return $this->state(fn (): array => ['status' => EventStatus::Active]);
    }

    public function completed(): self
    {
        return $this->state(fn (): array => ['status' => EventStatus::Completed]);
    }

    public function cancelled(): self
    {
        return $this->state(fn (): array => ['status' => EventStatus::Cancelled]);
    }

    public function standingsVisible(): self
    {
        return $this->settings(['standings_visible' => true]);
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    public function settings(array $settings): self
    {
        return $this->state(fn (array $attributes): array => [
            'settings' => ($attributes['settings'] ?? new EventSettings())->with($settings),
        ]);
    }
}
