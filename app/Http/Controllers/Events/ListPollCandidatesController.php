<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\EventAttendeeResource;
use App\Models\Event;
use App\Models\EventPoll;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class ListPollCandidatesController extends Controller
{
    #[Endpoint('List Poll Candidates', 'The Attendees this Player may pick in this Poll: armies on the display table for a painting Poll, and the teams this Player actually played for a favourite-opponent Poll. A Bye shortens the list rather than appearing in it.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('poll', 'integer', 'The id of the poll.', example: 1)]
    public function __invoke(Request $request, Event $event, EventPoll $poll): AnonymousResourceCollection
    {
        abort_unless($poll->event_id === $event->getKey(), 404);

        $candidates = $poll->eligibleSubjects($request->user())
            ->with(['memberships.user', 'memberships.faction'])
            ->orderBy('id')
            ->get();

        return EventAttendeeResource::collection($candidates);
    }
}
