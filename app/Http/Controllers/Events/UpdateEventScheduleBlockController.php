<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\UpdateEventScheduleBlockRequest;
use App\Http\Resources\Events\EventScheduleBlockResource;
use App\Models\Event;
use App\Models\EventScheduleBlock;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class UpdateEventScheduleBlockController extends Controller
{
    #[Endpoint('Edit a Schedule Block', 'Organisers only. Moving a block across midnight moves it to the other day, because the day is derived rather than stored.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('block_id', 'integer', 'The id of the schedule block.', example: 1)]
    #[ResponseFromApiResource(EventScheduleBlockResource::class, model: EventScheduleBlock::class)]
    public function __invoke(UpdateEventScheduleBlockRequest $request, Event $event, EventScheduleBlock $block): EventScheduleBlockResource
    {
        $block->fill($request->validated())->save();

        return EventScheduleBlockResource::make($block->load('round'));
    }
}
