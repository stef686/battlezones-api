<?php

namespace App\Jobs;

use App\Models\EventAttendeeMembership;
use App\Models\Game;
use App\Models\Round;
use App\Models\User;
use App\Notifications\Events\RoundLiveNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Notification;

/**
 * Tell everyone in a Round that their pairing is up.
 *
 * Queued and chunked rather than fired inline: publishing is an Organiser
 * standing at the front of a hall waiting for a response, not a request that
 * should sit through a few hundred notification writes.
 */
class NotifyRoundIsLive implements ShouldQueue
{
    use Queueable;

    private const CHUNK = 50;

    public function __construct(public Round $round) {}

    public function handle(): void
    {
        $this->round->games()
            ->where('is_bye', false)
            ->chunkById(self::CHUNK, function (Collection $games): void {
                $playersByGame = $this->playersByGame($games);

                foreach ($games as $game) {
                    $players = $playersByGame->get($game->getKey());

                    if ($players === null) {
                        continue;
                    }

                    Notification::send($players, new RoundLiveNotification($game));
                }
            });
    }

    /**
     * One query per chunk rather than one per Game.
     *
     * @param  Collection<int, Game>  $games
     * @return SupportCollection<int, SupportCollection<int, User>>
     */
    private function playersByGame(Collection $games): SupportCollection
    {
        $memberships = EventAttendeeMembership::query()
            ->join('game_attendee', 'game_attendee.event_attendee_id', '=', 'event_attendee_user.event_attendee_id')
            ->whereIn('game_attendee.game_id', $games->modelKeys())
            ->select('game_attendee.game_id', 'event_attendee_user.user_id')
            ->get();

        $users = User::query()->whereIn('id', $memberships->pluck('user_id')->unique())->get()->keyBy('id');

        return $memberships
            ->groupBy('game_id')
            ->map(fn ($rows) => $rows
                ->map(fn ($row) => $users->get($row->user_id))
                ->filter()
                ->values());
    }
}
