<?php

namespace Database\Factories;

use App\Models\EventAttendee;
use App\Models\EventPoll;
use App\Models\EventVote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventVote>
 */
class EventVoteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_poll_id' => EventPoll::factory(),
            'voter_user_id' => User::factory(),
            'subject_event_attendee_id' => EventAttendee::factory(),
        ];
    }
}
