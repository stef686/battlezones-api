<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\RoundDetailResource;
use App\Models\Event;
use App\Models\Round;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
class ShowEventRoundController extends Controller
{
    #[Endpoint('Show Event Round', 'Round detail with games for an event. Draft rounds are visible to Organisers only.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('id', 'integer', 'The id of the round.', example: 1)]
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
                'members' => [['id' => 12, 'name' => 'Ada Lovelace', 'faction' => ['id' => 3, 'name' => 'Sons of Horus'], 'army_list_locked' => true]],
                'scores' => ['match-points' => 3, 'victory-points' => 85],
            ]],
        ]],
    ]])]
    public function __invoke(Request $request, Event $event, Round $round): RoundDetailResource
    {
        abort_unless($event->status->hasRoundsVisible(), 404);
        abort_if($round->isDraft() && ! $event->isOrganisedBy($request->user('sanctum')), 404);

        $round->load(['games' => fn ($q) => $q->orderBy('table_number'), 'games.attendees.memberships.user', 'games.attendees.memberships.faction', 'games.scores.scoreType', 'games.openResultFlag']);

        return RoundDetailResource::make($round);
    }
}
