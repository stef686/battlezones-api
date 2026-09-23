<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\ReplaceEventScoreTypes;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\ReplaceEventScoreTypesRequest;
use App\Http\Resources\Events\EventScoreTypeResource;
use App\Models\Event;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class ReplaceEventScoreTypesController extends Controller
{
    public function __construct(private ReplaceEventScoreTypes $replaceScoreTypes) {}

    #[Endpoint('Replace the Score Types', 'Organisers only. Send the complete ordered set: position sets the display order, and position among the columns counting for ranking sets the ranking order.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[Response(['data' => [[
        'id' => 7,
        'name' => 'Match Points',
        'abbreviation' => 'MP',
        'slug' => 'match-points',
        'sort_direction' => 'desc',
        'is_derived' => true,
        'is_primary' => false,
        'counts_for_ranking' => true,
        'ranking_order' => 1,
        'win_points' => '3.00',
        'draw_points' => '1.00',
        'loss_points' => '0.00',
        'display_order' => 0,
        'is_scored' => true,
    ]]])]
    public function __invoke(ReplaceEventScoreTypesRequest $request, Event $event): AnonymousResourceCollection
    {
        return EventScoreTypeResource::collection(
            $this->replaceScoreTypes->execute($event, $request->scoreTypes()),
        );
    }
}
