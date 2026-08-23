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
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class ShowMyGameController extends Controller
{
    #[Endpoint('Show My Current Game', 'The authenticated Player\'s Game and table number in the current Round. Null until that Round is published.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[Response(['data' => [
        'id' => 18,
        'table_number' => 5,
        'is_bye' => false,
        'round' => ['id' => 4, 'number' => 2, 'name' => 'Round 2'],
        'result' => [
            'submitted_at' => '2026-09-12T14:05:00+00:00',
            'submitted_by' => ['id' => 12, 'name' => 'Ada Lovelace'],
            'edited_at' => null,
            'edited_by' => null,
            'is_flagged' => false,
        ],
        'attendees' => [[
            'id' => 9,
            'name' => 'Ada and Grace',
            'members' => [['id' => 12, 'name' => 'Ada Lovelace', 'faction' => ['id' => 3, 'name' => 'Sons of Horus'], 'army_list' => 'Legion Tactical Squad, 10 models...']],
            'scores' => ['match-points' => 3, 'victory-points' => 85],
        ]],
    ]])]
    #[Response(['data' => null], description: 'The current Round is not published, or this Player is not playing in it.')]
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

        $game->load(['round', 'attendees.memberships.user', 'attendees.memberships.faction', 'scores.scoreType', 'submittedBy', 'editedBy', 'openResultFlag']);

        return GameDetailResource::make($game);
    }
}
