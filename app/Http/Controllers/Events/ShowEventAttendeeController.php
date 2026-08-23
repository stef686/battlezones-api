<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\EventAttendeeDetailResource;
use App\Models\Event;
use App\Models\EventAttendee;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
class ShowEventAttendeeController extends Controller
{
    #[Endpoint('Show Event Attendee', 'Attendee detail for a publicly visible event.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('id', 'integer', 'The id of the attendee.', example: 1)]
    #[ResponseFromApiResource(EventAttendeeDetailResource::class, model: EventAttendee::class)]
    public function __invoke(Event $event, EventAttendee $attendee): EventAttendeeDetailResource
    {
        abort_unless($event->status->isPubliclyVisible(), 404);

        $attendee->load(['memberships.user.clubs', 'memberships.faction', 'customFieldResponses.field', 'games.round', 'games.attendees.memberships.user', 'games.scores.scoreType']);

        return EventAttendeeDetailResource::make($attendee);
    }
}
