<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\UpdatePaintingEntryRequest;
use App\Http\Resources\Events\EventAttendeeResource;
use App\Models\Event;
use App\Models\EventAttendee;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class UpdatePaintingEntryController extends Controller
{
    #[Endpoint('Mark a Painting Entry', 'Organisers only. Entry and display number are separate fields, so someone walking the display table can tick teams off one-handed and number them later.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('attendee', 'integer', 'The id of the attendee.', example: 1)]
    public function __invoke(UpdatePaintingEntryRequest $request, Event $event, EventAttendee $attendee): EventAttendeeResource
    {
        $attendee->fill($request->validated())->save();

        return EventAttendeeResource::make($attendee->load(['memberships.user', 'memberships.faction']));
    }
}
