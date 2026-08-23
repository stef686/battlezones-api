<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\EventAttendeeDetailResource;
use App\Models\Event;
use App\Models\EventAttendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class RevealArmyListsController extends Controller
{
    #[Endpoint('Reveal a Team\'s Army Lists', 'Organisers only. Frees a team held hostage by a Player who never submitted.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('attendee', 'integer', 'The id of the attendee.', example: 1)]
    public function __invoke(Request $request, Event $event, EventAttendee $attendee): EventAttendeeDetailResource
    {
        Gate::authorize('organise', $event);

        $attendee->forceFill(['army_lists_revealed_at' => now()])->save();

        $attendee->load(['memberships.user.clubs', 'memberships.faction', 'customFieldResponses.field', 'games.round', 'games.attendees.memberships.user']);

        return EventAttendeeDetailResource::make($attendee);
    }
}
