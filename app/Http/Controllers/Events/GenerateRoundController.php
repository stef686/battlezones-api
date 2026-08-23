<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\GenerateRoundPairings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Events\RoundDetailResource;
use App\Models\Event;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class GenerateRoundController extends Controller
{
    #[Endpoint('Generate the next Round', "Organisers only. Pairs the field into a new Draft Round. Rejected while the current Round is unpublished or has results outstanding, and once the Event's round count is reached.")]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
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
            'attendees' => [[
                'id' => 9,
                'name' => 'Ada and Grace',
                'members' => [['id' => 12, 'name' => 'Ada Lovelace', 'faction' => ['id' => 3, 'name' => 'Sons of Horus']]],
                'scores' => ['match-points' => 3, 'victory-points' => 85],
            ]],
        ]],
    ]])]
    public function __invoke(Event $event, GenerateRoundPairings $generatePairings): RoundDetailResource
    {
        Gate::authorize('organise', $event);

        $round = $generatePairings->execute($event);

        $round->load([
            'games' => fn ($query) => $query->orderBy('table_number'),
            'games.attendees.memberships.user',
            'games.attendees.memberships.faction',
            'games.scores.scoreType',
        ]);

        return RoundDetailResource::make($round);
    }
}
