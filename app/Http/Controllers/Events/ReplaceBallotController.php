<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\ReplaceBallot;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\ReplaceBallotRequest;
use App\Models\Event;
use App\Models\EventPoll;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class ReplaceBallotController extends Controller
{
    public function __construct(private ReplaceBallot $replaceBallot) {}

    #[Endpoint('Replace your Ballot', 'Send the complete set of Attendees you are picking; an empty array clears your Ballot. In doubles both Players vote independently.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('poll', 'integer', 'The id of the poll.', example: 1)]
    #[Response(['data' => ['poll_id' => 1, 'attendee_ids' => [4, 9]]])]
    #[Response(['message' => 'Voting is not open for you in this poll.'], 422, 'The Poll is closed, or this Player may not vote in it.')]
    public function __invoke(ReplaceBallotRequest $request, Event $event, EventPoll $poll): JsonResponse
    {
        $attendeeIds = $request->attendeeIds();

        $this->replaceBallot->execute($poll, $request->user(), $attendeeIds);

        return response()->json(['data' => ['poll_id' => $poll->id, 'attendee_ids' => $attendeeIds]]);
    }
}
