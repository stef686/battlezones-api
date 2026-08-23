<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\StoreEventScheduleBlockRequest;
use App\Http\Resources\Events\EventScheduleBlockResource;
use App\Models\Event;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class StoreEventScheduleBlockController extends Controller
{
    #[Endpoint('Add a Schedule Block', 'Organisers only. The day the block appears under is derived from its start time in the Event timezone.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    public function __invoke(StoreEventScheduleBlockRequest $request, Event $event): EventScheduleBlockResource
    {
        $block = $event->scheduleBlocks()->create($request->validated());

        return EventScheduleBlockResource::make($block->load('round'));
    }
}
