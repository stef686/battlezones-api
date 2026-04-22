<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\RoundResource;
use App\Models\Event;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
class ListEventRoundsController extends Controller
{
    #[Endpoint('List Event Rounds', 'List rounds for an event. Only visible for Active/Completed events.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    public function __invoke(Event $event): AnonymousResourceCollection
    {
        abort_unless($event->status->hasRoundsVisible(), 404);

        $rounds = $event->rounds()
            ->orderBy('number')
            ->get();

        return RoundResource::collection($rounds);
    }
}
