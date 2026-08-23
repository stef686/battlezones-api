<?php

namespace App\Actions\Events;

use App\Enums\PollType;
use App\Models\EventAttendee;
use App\Models\EventPoll;
use App\Models\Game;
use App\Models\User;
use App\Notifications\Events\VotingOpenNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Tell a team their favourite-opponent voting has opened.
 *
 * Eligibility itself is derived on read, so this only decides who to tell and
 * when: the moment a Game gives one of its Attendees a result in every Round.
 */
class OpenFavouriteOpponentVoting
{
    public function forGame(Game $game): void
    {
        $event = $game->round->event;

        $poll = $event->polls()
            ->where('type', PollType::FavouriteOpponent->value)
            ->orderByDesc('id')
            ->first();

        if (! $poll instanceof EventPoll) {
            return;
        }

        // An Organiser has finalised at day's end; nobody's voting is opening now.
        if ($poll->closes_at !== null && $poll->closes_at->isPast()) {
            return;
        }

        foreach ($game->attendees as $attendee) {
            if (! $event->hasCompletedEveryRound($attendee)) {
                continue;
            }

            $this->notify($poll, $attendee);
        }
    }

    /**
     * Once per Player, not once per Game they finish.
     *
     * The check reads the stored notifications rather than a new column: a
     * "told them already" flag is one more thing to write on every path that
     * could complete a team.
     */
    private function notify(EventPoll $poll, EventAttendee $attendee): void
    {
        $players = User::query()
            ->whereIn('id', $attendee->memberships()->select('user_id'))
            ->get()
            ->reject(fn (User $player): bool => $player->notifications()
                ->where('type', VotingOpenNotification::class)
                ->where('data->poll_id', $poll->getKey())
                ->exists());

        Notification::send($players, new VotingOpenNotification($poll));
    }
}
