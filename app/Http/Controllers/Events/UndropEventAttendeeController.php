<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\UndropAttendee;
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
class UndropEventAttendeeController extends Controller
{
    public function __construct(private UndropAttendee $undropAttendee) {}

    #[Endpoint('Undrop a Team', 'Organisers only. Clears a Drop, so the Attendee is paired again from the next Round generated. An Attendee that is not Dropped is refused with 422.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('attendee_id', 'integer', 'The id of the attendee.', example: 1)]
    #[Response(status: 200, content: ['data' => [
        'id' => 9,
        'name' => 'Ada and Grace',
        'allegiance' => 'loyalist',
        'avatar' => null,
        'allegiance_locked' => true,
        'has_been_paired' => true,
        'dropped_at' => null,
        'paired_in_draft_round' => false,
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
    #[Response(status: 422, content: ['message' => 'This team has not been dropped.', 'errors' => ['attendee' => ['This team has not been dropped.']]])]
    public function __invoke(Request $request, Event $event, EventAttendee $attendee): EventAttendeeDetailResource
    {
        abort_unless($event->isVisibleTo($request->user()), 404);

        // The same people who may drop a team may take the Drop back.
        Gate::authorize('drop', $attendee);

        $this->undropAttendee->execute($attendee);

        return EventAttendeeDetailResource::make($attendee->loadForDetail());
    }
}
