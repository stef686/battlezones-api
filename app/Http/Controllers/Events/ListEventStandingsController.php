<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\ListEventStandingsRequest;
use App\Http\Resources\Events\EventStandingResource;
use App\Models\Event;
use App\Models\EventScoreType;
use App\Models\EventStanding;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
class ListEventStandingsController extends Controller
{
    #[Endpoint('List Event Standings', 'Paginated standings for a publicly visible event with optional search and sorting.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[ResponseFromApiResource(EventStandingResource::class, model: EventStanding::class, paginate: 15)]
    public function __invoke(ListEventStandingsRequest $request, Event $event): AnonymousResourceCollection
    {
        abort_unless($event->status->isPubliclyVisible(), 404);
        abort_unless($event->standings_visible, 404);

        $query = $event->standings()
            ->with(['attendee.user.clubs', 'attendee.faction', 'scores.scoreType']);

        if ($request->filled('search')) {
            $term = '%'.$request->string('search').'%';

            $query->whereHas('attendee', function (Builder $q) use ($term): void {
                $q->where(function (Builder $q) use ($term): void {
                    $q->whereHas('user', fn (Builder $q) => $q->where('users.name', 'like', $term))
                        ->orWhereHas('faction', fn (Builder $q) => $q->where('factions.name', 'like', $term))
                        ->orWhereHas('user.clubs', fn (Builder $q) => $q->where('clubs.name', 'like', $term));
                });
            });
        }

        if ($request->filled('sort_by')) {
            $scoreType = $event->scoreTypes()->where('slug', $request->input('sort_by'))->first();

            abort_unless($scoreType instanceof EventScoreType, 422);

            $query->join('event_standing_scores', function ($join) use ($scoreType): void {
                $join->on('event_standings.id', '=', 'event_standing_scores.event_standing_id')
                    ->where('event_standing_scores.event_score_type_id', '=', $scoreType->id);
            })
                ->orderBy('event_standing_scores.value', $scoreType->sort_direction->value)
                ->select('event_standings.*');
        } else {
            $query->orderBy('position');
        }

        $standings = $query->paginate();

        return EventStandingResource::collection($standings);
    }
}
