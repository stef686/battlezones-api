<?php

namespace App\Http\Controllers\Events;

use App\Events\ResultFlagResolved;
use App\Http\Controllers\Controller;
use App\Http\Resources\Events\GameResultFlagResource;
use App\Models\Event;
use App\Models\Game;
use App\Models\GameResultFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class ResolveGameResultFlagController extends Controller
{
    #[Endpoint('Resolve a Flagged Result', 'Organisers only. Closes the open flag on a Game. Resolving is deliberately separate from editing: an Organiser who checks a flag and finds the original result was right still needs a way to clear it.')]
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
    public function __invoke(Request $request, Event $event, Game $game): GameResultFlagResource
    {
        Gate::authorize('organise', $event);

        abort_unless($game->round->event_id === $event->getKey(), 404);

        $flag = $game->openResultFlag()->first();

        abort_unless($flag instanceof GameResultFlag, 404, 'There is no open flag on this game.');

        $flag->forceFill([
            'resolved_at' => now(),
            'resolved_by_user_id' => $request->user()->getKey(),
        ])->save();

        ResultFlagResolved::dispatch($flag, $request->user());

        return GameResultFlagResource::make($flag->load(['flaggedBy', 'resolvedBy']));
    }
}
