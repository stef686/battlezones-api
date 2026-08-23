<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventPoll;
use App\Models\EventVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class ShowEventPollResultsController extends Controller
{
    #[Endpoint('Read Poll Tallies', 'Organisers only, permanently — not live, not after close, not to Players. Winners are announced in the venue, and the announcement is an Event update. Ties come back unresolved: which of two equal armies wins is a judgement, not a rule to invent in code.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('poll_id', 'integer', 'The id of the poll.', example: 1)]
    #[Response(['data' => [
        'poll' => ['id' => 1, 'name' => 'Best Painted Army', 'type' => 'best_painted', 'is_open' => false],
        'tallies' => [[
            'attendee' => ['id' => 9, 'name' => 'Ada and Grace', 'display_number' => 4],
            'votes' => 11,
        ]],
    ]])]
    public function __invoke(Event $event, EventPoll $poll): JsonResponse
    {
        Gate::authorize('organise', $event);

        abort_unless($poll->event_id === $event->getKey(), 404);

        $counts = EventVote::query()
            ->where('event_poll_id', $poll->getKey())
            ->selectRaw('subject_event_attendee_id, count(*) as votes')
            ->groupBy('subject_event_attendee_id')
            ->pluck('votes', 'subject_event_attendee_id');

        $attendees = EventAttendee::query()
            ->whereIn('id', $counts->keys())
            ->with('memberships.user')
            ->get();

        $tallies = $attendees
            ->map(fn (EventAttendee $attendee): array => [
                'attendee' => [
                    'id' => $attendee->id,
                    'name' => $attendee->displayName(),
                    'display_number' => $attendee->display_number,
                ],
                'votes' => (int) $counts->get($attendee->id, 0),
            ])
            ->sortByDesc('votes')
            ->values();

        return response()->json([
            'data' => [
                'poll' => [
                    'id' => $poll->id,
                    'name' => $poll->name,
                    'type' => $poll->type->value,
                    'is_open' => $poll->isOpen(),
                ],
                'tallies' => $tallies,
            ],
        ]);
    }
}
