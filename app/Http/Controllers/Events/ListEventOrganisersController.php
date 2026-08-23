<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\EventOrganiserResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class ListEventOrganisersController extends Controller
{
    #[Endpoint('List Event Organisers', 'The Players trusted to run this event. Organisers only.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    public function __invoke(Request $request, Event $event): AnonymousResourceCollection
    {
        Gate::authorize('organise', $event);

        return EventOrganiserResource::collection($event->organisers()->orderBy('users.name')->get());
    }
}
