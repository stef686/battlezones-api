<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\EventScheduleBlockResource;
use App\Models\Event;
use App\Models\EventScheduleBlock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
class ListEventScheduleController extends Controller
{
    #[Endpoint('List the Event Schedule', "The Event's schedule grouped by day in the Event's own timezone, each day's blocks in time order.")]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    public function __invoke(Request $request, Event $event): JsonResponse
    {
        abort_unless($event->status->isPubliclyVisible(), 404);

        $blocks = $event->scheduleBlocks()
            ->with('round')
            ->orderBy('starts_at')
            ->orderBy('display_order')
            ->orderBy('id')
            ->get()
            ->each(fn (EventScheduleBlock $block) => $block->setRelation('event', $event));

        $days = $blocks
            ->groupBy(fn (EventScheduleBlock $block): string => $block->day())
            ->map(fn ($dayBlocks, string $date): array => [
                'date' => $date,
                'blocks' => EventScheduleBlockResource::collection($dayBlocks)->resolve($request),
            ])
            ->values();

        return response()->json(['data' => $days]);
    }
}
