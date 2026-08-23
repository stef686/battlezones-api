<?php

namespace App\Actions\Events;

use App\Models\EventPoll;
use App\Models\EventVote;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Replace a Player's whole Ballot in one write.
 *
 * Casting votes one at a time would make the per-Player limit a check-then-
 * write race, and "change my mind about my second pick" a different call from
 * "vote". Here both are the same call.
 */
class ReplaceBallot
{
    /**
     * @param  list<int>  $attendeeIds
     */
    public function execute(EventPoll $poll, User $voter, array $attendeeIds): void
    {
        DB::transaction(function () use ($poll, $voter, $attendeeIds): void {
            $poll->votes()->where('voter_user_id', $voter->getKey())->delete();

            foreach ($attendeeIds as $attendeeId) {
                EventVote::query()->create([
                    'event_poll_id' => $poll->getKey(),
                    'voter_user_id' => $voter->getKey(),
                    'subject_event_attendee_id' => $attendeeId,
                ]);
            }
        });
    }
}
