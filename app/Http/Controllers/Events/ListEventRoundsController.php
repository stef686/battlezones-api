<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\RoundResource;
use App\Models\Event;
use App\Models\Round;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
class ListEventRoundsController extends Controller
{
    #[Endpoint('List Event Rounds', 'List rounds for an event. Only visible for Active/Completed events. Draft rounds are shown to Organisers only.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[ResponseFromApiResource(RoundResource::class, model: Round::class, collection: true)]
    public function __invoke(Request $request, Event $event): AnonymousResourceCollection
    {
        abort_unless($event->status->hasRoundsVisible(), 404);

        $rounds = $event->rounds()
            ->unless($event->isOrganisedBy($request->user('sanctum')), fn (Builder $query) => $query->live())
            ->orderBy('number')
            ->get();

        return RoundResource::collection($rounds);
    }
}
