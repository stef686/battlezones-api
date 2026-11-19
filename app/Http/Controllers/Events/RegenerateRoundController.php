<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\GenerateRoundPairings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Events\RoundDetailResource;
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
class RegenerateRoundController extends Controller
{
    public function __construct(private GenerateRoundPairings $generatePairings) {}

    #[Endpoint('Pair a Draft Round again', 'Organisers only, on the latest Round while it is a Draft with no results. Replaces its Games with a fresh Pairing of the field as it stands, leaving out Attendees Dropped since and including any added since. The Round keeps its id, number and schedule block. Refused with the same reasons as generation when the field cannot be paired, in which case the old Pairing is left exactly as it was.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('round_id', 'integer', 'The id of the round.', example: 1)]
    #[Response(['data' => [
        'id' => 4,
        'number' => 2,
        'name' => 'Round 2',
        'status' => 'draft',
        'games' => [[
            'id' => 18,
            'table_number' => 1,
            'is_bye' => false,
            'is_rematch' => false,
            'result' => ['submitted_at' => null, 'is_flagged' => false],
            'attendees' => [[
                'id' => 9,
                'name' => 'Ada and Grace',
                'allegiance' => 'loyalist',
                'members' => [['id' => 12, 'name' => 'Ada Lovelace', 'faction' => ['id' => 3, 'name' => 'Sons of Horus'], 'army_list_locked' => false]],
                'scores' => [],
            ]],
        ]],
    ]])]
    #[Response(['message' => 'Round 2 already has results, so it cannot be paired again.'], status: 422)]
    public function __invoke(Event $event, Round $round): RoundDetailResource
    {
        Gate::authorize('organise', $event);

        $round = $this->generatePairings->regenerate($round);

        $round->loadDetail();

        return RoundDetailResource::make($round);
    }
}
