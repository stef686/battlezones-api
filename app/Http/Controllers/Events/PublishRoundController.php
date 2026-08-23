<?php

namespace App\Http\Controllers\Events;

use App\Enums\RoundStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Events\RoundDetailResource;
use App\Jobs\NotifyRoundIsLive;
use App\Models\Event;
use App\Models\Round;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class PublishRoundController extends Controller
{
    #[Endpoint('Publish a Round', 'Organisers only. Makes the Round\'s pairings and table numbers visible to Players. Earlier Rounds stay Live.')]
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
    public function __invoke(Event $event, Round $round): RoundDetailResource
    {
        Gate::authorize('organise', $event);

        $round->update(['status' => RoundStatus::Live]);

        NotifyRoundIsLive::dispatch($round);

        $round->load(['games' => fn ($query) => $query->orderBy('table_number'), 'games.attendees.memberships.user', 'games.attendees.memberships.faction', 'games.scores.scoreType', 'games.openResultFlag']);

        return RoundDetailResource::make($round);
    }
}
