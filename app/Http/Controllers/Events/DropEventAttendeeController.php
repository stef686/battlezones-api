<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\DropAttendee;
use App\Http\Controllers\Controller;
use App\Http\Resources\Events\EventAttendeeDetailResource;
use App\Models\Event;
use App\Models\EventAttendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class DropEventAttendeeController extends Controller
{
    public function __construct(private DropAttendee $dropAttendee) {}

    #[Endpoint('Drop a Team', 'Organisers only. Marks a paired Attendee as having left: it is left out of every Round generated afterwards, and everything already played stands. A never-paired Attendee is refused with 422 and should be deleted instead.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('attendee_id', 'integer', 'The id of the attendee.', example: 1)]
    #[Response(status: 200, content: ['data' => [
        'id' => 9,
        'name' => 'Ada and Grace',
        'allegiance' => 'loyalist',
        'avatar' => null,
        'allegiance_locked' => true,
        'has_been_paired' => true,
        'dropped_at' => '2026-09-12T13:05:00Z',
        'members' => [[
            'id' => 12,
            'name' => 'Ada Lovelace',
            'faction' => ['id' => 3, 'name' => 'Sons of Horus'],
            'army_list_locked' => true,
            'clubs' => [],
        ]],
        'checked_in_at' => null,
        'custom_field_responses' => [],
        'games' => [],
    ]])]
    #[Response(status: 422, content: ['message' => 'This team has already been dropped.', 'errors' => ['attendee' => ['This team has already been dropped.']]])]
    public function __invoke(Request $request, Event $event, EventAttendee $attendee): EventAttendeeDetailResource
    {
        abort_unless($event->isVisibleTo($request->user()), 404);

        Gate::authorize('drop', $attendee);

        $this->dropAttendee->execute($attendee);

        $attendee->load(['memberships.user.clubs', 'memberships.faction', 'customFieldResponses.field', 'games.round', 'games.attendees.memberships.user', 'games.scores.scoreType']);

        return EventAttendeeDetailResource::make($attendee);
    }
}
