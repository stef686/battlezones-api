<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventScheduleBlock;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class DeleteEventScheduleBlockController extends Controller
{
    #[Endpoint('Delete a Schedule Block', 'Organisers only.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[UrlParam('block', 'integer', 'The id of the schedule block.', example: 1)]
    public function __invoke(Event $event, EventScheduleBlock $block): Response
    {
        Gate::authorize('organise', $event);

        abort_unless($block->event_id === $event->getKey(), 404);

        $block->delete();

        return response()->noContent();
    }
}
