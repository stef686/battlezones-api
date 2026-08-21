<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\GameDetailResource;
use App\Models\Event;
use App\Models\Game;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class ShowMyGameController extends Controller
{
    #[Endpoint('Show My Current Game', 'The authenticated Player\'s Game and table number in the current Round. Null until that Round is published.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    public function __invoke(Request $request, Event $event): GameDetailResource|JsonResponse
    {
        abort_unless($event->status->hasRoundsVisible(), 404);

        $round = $event->currentRound();

        $game = $round?->games()
            ->whereHas('attendees.memberships', fn (Builder $query) => $query->where('user_id', $request->user()->getKey()))
            ->first();

        if (! $game instanceof Game) {
            return response()->json(['data' => null]);
        }

        $game->load(['round', 'attendees.memberships.user', 'attendees.memberships.faction', 'scores.scoreType']);

        return GameDetailResource::make($game);
    }
}
