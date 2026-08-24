<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class ReorderEventScheduleController extends Controller
{
    #[Endpoint('Reorder Schedule Blocks', 'Organisers only. Sets the order blocks appear in when they start at the same time — two things at ten o\'clock still need an order on the page.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[BodyParam('block_ids', 'integer[]', 'The block ids in the order they should appear.', required: true, example: [3, 1, 2])]
    #[Response(['data' => ['block_ids' => [3, 1, 2]]])]
    public function __invoke(Request $request, Event $event): JsonResponse
    {
        Gate::authorize('organise', $event);

        $validated = $request->validate([
            'block_ids' => ['required', 'array', 'min:1'],
            'block_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $ids = array_map(intval(...), $validated['block_ids']);

        $blocks = $event->scheduleBlocks()->whereIn('id', $ids)->get();

        abort_unless($blocks->count() === count($ids), 404);

        foreach ($ids as $order => $id) {
            $blocks->firstWhere('id', $id)?->forceFill(['display_order' => $order])->save();
        }

        return response()->json(['data' => ['block_ids' => $ids]]);
    }
}
