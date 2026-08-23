<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\EventPollResource;
use App\Models\Event;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class ListEventPollsController extends Controller
{
    #[Endpoint('List Polls', "The Event's Polls and whether each is open. Tallies are not here, and are never readable by Players.")]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    public function __invoke(Event $event): AnonymousResourceCollection
    {
        abort_unless($event->status->isPubliclyVisible(), 404);

        return EventPollResource::collection($event->polls()->orderBy('id')->get());
    }
}
