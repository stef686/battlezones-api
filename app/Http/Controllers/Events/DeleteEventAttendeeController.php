<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\DeleteAttendee;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventAttendee;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response as ResponseAttribute;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class DeleteEventAttendeeController extends Controller
{
    public function __construct(private DeleteAttendee $deleteAttendee) {}

    #[Endpoint('Delete a Team', 'Organisers only. Removes an Attendee that has never been in a Game, Draft Rounds included; a paired Attendee is refused with 422 and should be Dropped instead.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('attendee_id', 'integer', 'The id of the attendee.', example: 1)]
    #[ResponseAttribute(status: 204, description: 'The Attendee was deleted.')]
    #[ResponseAttribute(status: 422, content: ['message' => 'This team has been paired, so it cannot be deleted. Drop it instead.', 'errors' => ['attendee' => ['This team has been paired, so it cannot be deleted. Drop it instead.']]])]
    public function __invoke(Request $request, Event $event, EventAttendee $attendee): Response
    {
        abort_unless($event->isVisibleTo($request->user()), 404);

        Gate::authorize('delete', $attendee);

        $this->deleteAttendee->execute($attendee);

        return response()->noContent();
    }
}
