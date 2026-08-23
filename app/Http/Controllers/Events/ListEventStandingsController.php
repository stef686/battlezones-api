<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\ListEventStandingsRequest;
use App\Http\Resources\Events\EventStandingResource;
use App\Models\Event;
use App\Models\EventScoreType;
use App\Queries\EventStandingsQuery;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
class ListEventStandingsController extends Controller
{
    #[Endpoint('List Event Standings', 'Paginated standings for a publicly visible event, computed from Games. Ranked on Match Points then Victory Points, with tied Attendees sharing a position. Sorting by a Score Type changes the order of the list but never the reported position.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[Response(['data' => [[
        'id' => 9,
        'position' => 1,
        'attendee' => [
            'id' => 9,
            'name' => 'Ada and Grace',
            'members' => [[
                'id' => 12,
                'name' => 'Ada Lovelace',
                'faction' => ['id' => 3, 'name' => 'Sons of Horus'],
                'clubs' => [['id' => 2, 'name' => 'The Ordo Ludi']],
            ]],
        ],
        'scores' => [[
            'value' => 6,
            'score_type' => ['id' => 1, 'name' => 'Match Points', 'slug' => 'match-points', 'sort_direction' => 'desc'],
        ]],
    ]]])]
    public function __invoke(ListEventStandingsRequest $request, Event $event): AnonymousResourceCollection
    {
        abort_unless($event->status->isPubliclyVisible(), 404);
        abort_unless($event->settings->standingsVisible, 404);

        $sortBy = null;

        if ($request->filled('sort_by')) {
            $sortBy = $event->scoreTypes()->where('slug', $request->string('sort_by'))->first();

            abort_unless($sortBy instanceof EventScoreType, 422);
        }

        $standings = EventStandingsQuery::forEvent($event)
            ->search($request->string('search')->toString() ?: null)
            ->sortBy($sortBy)
            ->paginate();

        return EventStandingResource::collection($standings);
    }
}
