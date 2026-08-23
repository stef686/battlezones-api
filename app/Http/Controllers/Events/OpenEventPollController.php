<?php

namespace App\Http\Controllers\Events;

use App\Events\PollOpened;
use App\Http\Controllers\Controller;
use App\Http\Resources\Events\EventPollResource;
use App\Models\Event;
use App\Models\EventPoll;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class OpenEventPollController extends Controller
{
    #[Endpoint('Open a Poll', "Organisers only. Opens this Poll's voting window, independently of every other Poll.")]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('poll', 'integer', 'The id of the poll.', example: 1)]
    #[ResponseFromApiResource(EventPollResource::class, model: EventPoll::class)]
    public function __invoke(Event $event, EventPoll $poll): EventPollResource
    {
        Gate::authorize('organise', $event);

        abort_unless($poll->event_id === $event->getKey(), 404);

        $poll->forceFill(['opens_at' => now(), 'closes_at' => null])->save();

        PollOpened::dispatch($poll);

        return EventPollResource::make($poll);
    }
}
