<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\SwapRoundPairings;
use App\Exceptions\CannotSwapPairing;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\SwapRoundPairingsRequest;
use App\Http\Resources\Events\RoundDetailResource;
use App\Models\Event;
use App\Models\Game;
use App\Models\Round;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class SwapRoundPairingsController extends Controller
{
    public function __construct(private SwapRoundPairings $swapPairings) {}

    #[Endpoint('Swap two Pairings', 'Organisers only, on a Draft Round. Exchanges the same-allegiance side between two Games, or moves the Bye when one of them is a Bye. Table numbers stay with the Game, and rematch flags are recomputed.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('round_id', 'integer', 'The id of the round.', example: 1)]
    #[Response(['data' => [
        'id' => 4,
        'number' => 2,
        'name' => 'Round 2',
        'status' => 'live',
        'games' => [[
            'id' => 18,
            'table_number' => 5,
            'is_bye' => false,
            'is_rematch' => false,
            'result' => ['submitted_at' => null, 'is_flagged' => false],
            'attendees' => [[
                'id' => 9,
                'name' => 'Ada and Grace',
                'allegiance' => 'loyalist',
                'members' => [['id' => 12, 'name' => 'Ada Lovelace', 'faction' => ['id' => 3, 'name' => 'Sons of Horus']]],
                'scores' => ['match-points' => 3, 'victory-points' => 85],
            ]],
        ]],
    ]])]
    public function __invoke(SwapRoundPairingsRequest $request, Event $event, Round $round): RoundDetailResource
    {
        [$firstId, $secondId] = $request->collect('game_ids')->map(fn (mixed $id): int => (int) $id)->all();

        $first = Game::query()->find($firstId);
        $second = Game::query()->find($secondId);

        if (! $first instanceof Game || ! $second instanceof Game) {
            throw CannotSwapPairing::gameNotInRound();
        }

        $this->swapPairings->execute($round, $first, $second);

        $round->load([
            'games' => fn ($query) => $query->orderBy('table_number'),
            'games.attendees.memberships.user',
            'games.attendees.memberships.faction',
            'games.scores.scoreType', 'games.openResultFlag',
        ]);

        return RoundDetailResource::make($round);
    }
}
