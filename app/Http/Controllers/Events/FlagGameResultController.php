<?php

namespace App\Http\Controllers\Events;

use App\Events\ResultFlagged;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\FlagGameResultRequest;
use App\Http\Resources\Events\GameResultFlagResource;
use App\Models\Event;
use App\Models\Game;
use App\Models\GameResultFlag;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class FlagGameResultController extends Controller
{
    #[Endpoint('Flag a Game Result', 'A Player in the Game, or an Organiser, claims the submitted result is wrong. Flagging again while a flag is open returns the open flag rather than raising a second one.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('game', 'integer', 'The id of the game.', example: 1)]
    #[Response(['data' => [
        'id' => 3,
        'game_id' => 18,
        'reason' => 'We agreed 85-70 but it went in the other way round.',
        'flagged_at' => '2026-09-12T14:20:00+00:00',
        'flagged_by' => ['id' => 12, 'name' => 'Ada Lovelace'],
        'resolved_at' => null,
        'resolved_by' => null,
    ]])]
    public function __invoke(FlagGameResultRequest $request, Event $event, Game $game): GameResultFlagResource
    {
        $flag = GameResultFlag::query()->firstOrCreate(
            [
                'game_id' => $game->getKey(),
                'resolved_at' => null,
            ],
            [
                'flagged_by_user_id' => $request->user()->getKey(),
                'reason' => $request->string('reason')->toString() ?: null,
            ],
        );

        if ($flag->wasRecentlyCreated) {
            ResultFlagged::dispatch($flag);
        }

        return GameResultFlagResource::make($flag->load('flaggedBy'));
    }
}
