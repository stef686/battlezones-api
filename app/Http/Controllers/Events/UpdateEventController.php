<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\UpdateEventRequest;
use App\Http\Resources\Events\EventResource;
use App\Models\Event;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class UpdateEventController extends Controller
{
    #[Endpoint('Edit an Event', 'Organisers only. Moving the dates leaves every Schedule block where it is: blocks store absolute times, and a venue change usually affects one day rather than all of them.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[ResponseFromApiResource(EventResource::class, model: Event::class)]
    public function __invoke(UpdateEventRequest $request, Event $event): EventResource
    {
        $event->fill($request->validated())->save();

        $event->load(['gameSystem', 'documents']);
        $event->loadCount('attendees');

        return EventResource::make($event)->withViewer();
    }
}
