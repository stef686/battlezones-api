<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\UnpublishEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\Events\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class UnpublishEventController extends Controller
{
    public function __construct(private UnpublishEvent $unpublishEvent) {}

    #[Endpoint('Unpublish an Event', 'Lead Organiser only. Takes a Published Event back to Draft, undoing a publish made too early. Refused with 422 for anything but a Published Event, and once anybody has entered or an Invite is outstanding: cancel the Event instead.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[ResponseFromApiResource(EventResource::class, model: Event::class)]
    public function __invoke(Request $request, Event $event): EventResource
    {
        abort_unless($event->isVisibleTo($request->user()), 404);

        Gate::authorize('unpublish', $event);

        $this->unpublishEvent->execute($event);

        $event->load(['gameSystem', 'documents']);
        $event->loadCount('attendees');

        return EventResource::make($event)->withViewer();
    }
}
