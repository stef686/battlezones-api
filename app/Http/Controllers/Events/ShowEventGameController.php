<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\GameDetailResource;
use App\Models\Event;
use App\Models\Game;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
class ShowEventGameController extends Controller
{
    #[Endpoint('Show Event Game', 'Game detail with full score breakdown and army lists. Games in a Draft round are visible to Organisers only.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('id', 'integer', 'The id of the game.', example: 1)]
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
    public function __invoke(Request $request, Event $event, Game $game): GameDetailResource
    {
        abort_unless($event->status->isPubliclyVisible(), 404);

        $game->load(['round', 'attendees.memberships.user', 'attendees.memberships.faction', 'scores.scoreType', 'submittedBy', 'editedBy', 'openResultFlag']);

        abort_unless($game->round->event_id === $event->id, 404);
        abort_if($game->round->isDraft() && ! $event->isOrganisedBy($request->user('sanctum')), 404);

        return GameDetailResource::make($game);
    }
}
