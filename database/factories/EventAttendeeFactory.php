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
            'name' => null,
            'allegiance' => null,
            'checked_in_at' => null,
        ];
    }

    /**
     * Add a Player to the party, with their own Faction and army list.
     *
     * @param  array<string, mixed>  $membership
     */
    public function withMember(User|Factory|null $user = null, array $membership = []): self
    {
        return $this->afterCreating(function (EventAttendee $attendee) use ($user, $membership): void {
            $player = match (true) {
                $user instanceof User => $user,
                $user instanceof Factory => $user->create(),
                default => User::factory()->create(),
            };

            $attendee->members()->attach($player, [
                'event_id' => $attendee->event_id,
                'faction_id' => $membership['faction_id'] ?? Faction::factory()->create()->id,
                'army_list' => $membership['army_list'] ?? fake()->paragraph(),
            ]);
        });
    }
}
