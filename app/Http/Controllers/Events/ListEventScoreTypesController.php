<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\EventScoreTypeResource;
use App\Models\Event;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class ListEventScoreTypesController extends Controller
{
    #[Endpoint('List Score Types', 'Organisers only. The columns this Event is scored on, in the order they are shown, with the points behind a derived column and whether any Game has been scored under it yet.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[Response(['data' => [[
        'id' => 1,
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
    public function __invoke(Event $event): AnonymousResourceCollection
    {
        Gate::authorize('organise', $event);

        $scoreTypes = $event->scoreTypes()
            ->withExists('scores')
            ->orderBy('display_order')
            ->get();

        return EventScoreTypeResource::collection($scoreTypes);
    }
}
