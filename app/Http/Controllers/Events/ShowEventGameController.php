<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\GameDetailResource;
use App\Models\Event;
use App\Models\Game;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
class ShowEventGameController extends Controller
{
    #[Endpoint('Show Event Game', 'Game detail with full score breakdown and army lists. Games in a Draft round are visible to Organisers only.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('game', 'integer', 'The id of the game.', example: 1)]
    public function __invoke(Request $request, Event $event, Game $game): GameDetailResource
    {
        abort_unless($event->status->isPubliclyVisible(), 404);

        $game->load(['round', 'attendees.memberships.user', 'attendees.memberships.faction', 'scores.scoreType']);

        abort_unless($game->round->event_id === $event->id, 404);
        abort_if($game->round->isDraft() && ! $event->isOrganisedBy($request->user('sanctum')), 404);

        return GameDetailResource::make($game);
    }
}
