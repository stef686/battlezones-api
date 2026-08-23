<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\StoreEventPollRequest;
use App\Http\Resources\Events\EventPollResource;
use App\Models\Event;
use App\Models\EventPoll;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class StoreEventPollController extends Controller
{
    #[Endpoint('Create a Poll', 'Organisers only. A Poll opens closed: the window is set by opening it, not by creating it.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[ResponseFromApiResource(EventPollResource::class, model: EventPoll::class)]
    public function __invoke(StoreEventPollRequest $request, Event $event): EventPollResource
    {
        $poll = $event->polls()->create($request->validated());

        return EventPollResource::make($poll);
    }
}
