<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\UpdateEventAttendeeRequest;
use App\Http\Resources\Events\EventAttendeeDetailResource;
use App\Models\Event;
use App\Models\EventAttendee;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class UpdateEventAttendeeController extends Controller
{
    #[Endpoint('Amend a Team', 'Members and Organisers may change the party name; allegiance freezes once a Round is Live.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('attendee', 'integer', 'The id of the attendee.', example: 1)]
    public function __invoke(
        UpdateEventAttendeeRequest $request,
        Event $event,
        EventAttendee $attendee,
    ): EventAttendeeDetailResource {
        Gate::authorize('update', $attendee);

        $attendee->fill($request->validated())->save();

        $attendee->load(['memberships.user.clubs', 'memberships.faction', 'customFieldResponses.field', 'games.round', 'games.attendees.memberships.user']);

        return EventAttendeeDetailResource::make($attendee);
    }
}
