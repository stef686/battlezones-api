<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Events', 'APIs for Events')]
class ShowEventController extends Controller
{
    #[Endpoint('Show Event', 'Get a publicly visible event by slug.')]
    #[ResponseFromApiResource(EventResource::class, model: Event::class)]
    public function __invoke(Request $request, Event $event): EventResource
    {
        abort_unless($event->status->isPubliclyVisible(), 404);

        $event->load(['gameSystem', 'documents']);

        return EventResource::make($event);
    }
}
