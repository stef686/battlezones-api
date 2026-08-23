<?php

namespace App\Actions\Events;

use App\Enums\PollType;
use App\Models\EventAttendee;
use App\Models\EventPoll;
use App\Models\Game;
use App\Models\User;
use App\Notifications\Events\VotingOpenNotification;
use Illuminate\Notifications\DatabaseNotification;
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
            ->get();

        $alreadyTold = $this->alreadyNotified($poll, $players->modelKeys());

        $players = $players->reject(
            fn (User $player): bool => in_array($player->getKey(), $alreadyTold, true)
        );

        Notification::send($players, new VotingOpenNotification($poll));
    }

    /**
     * The ids, of those given, already told about this poll.
     *
     * One query for the team rather than one per player: a full Event runs
     * this for every Attendee as their last Game lands.
     *
     * @param  list<int>  $userIds
     * @return list<int>
     */
    private function alreadyNotified(EventPoll $poll, array $userIds): array
    {
        return DatabaseNotification::query()
            ->where('notifiable_type', (new User())->getMorphClass())
            ->whereIn('notifiable_id', $userIds)
            ->where('type', VotingOpenNotification::class)
            ->where('data->poll_id', $poll->getKey())
            ->pluck('notifiable_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();
    }
}
