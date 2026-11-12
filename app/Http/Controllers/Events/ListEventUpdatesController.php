<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\EventUpdateResource;
use App\Models\Event;
use App\Models\EventUpdate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Events', 'APIs for Events')]
class ListEventUpdatesController extends Controller
{
    #[Endpoint('List Event Updates', 'List updates for an event, pinned first then most recent. A Draft or Cancelled Event answers 404 to everyone but its Organisers.')]
    #[ResponseFromApiResource(EventUpdateResource::class, model: EventUpdate::class, paginate: 15)]
    public function __invoke(Request $request, Event $event): AnonymousResourceCollection
    {
        abort_unless($event->isVisibleTo($request->user('sanctum')), 404);

        $updates = $event->updates()
            ->with(['author', 'attachments'])
            ->orderByRaw('pinned_at IS NULL')
            ->orderByDesc('pinned_at')
            ->orderByDesc('published_at')
            ->paginate();

        return EventUpdateResource::collection($updates);
    }
}
