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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Group('Events', 'APIs for Events')]
class ListEventUpdatesController extends Controller
{
    #[Endpoint('List Event Updates', 'List updates for a publicly visible event, pinned first then most recent.')]
    #[ResponseFromApiResource(EventUpdateResource::class, model: EventUpdate::class, paginate: 15)]
    public function __invoke(Request $request, Event $event): AnonymousResourceCollection
    {
        if (! $event->status->isPubliclyVisible()) {
            throw new NotFoundHttpException();
        }

        $updates = $event->updates()
            ->with(['author', 'attachments'])
            ->orderByRaw('pinned_at IS NULL')
            ->orderByDesc('pinned_at')
            ->orderByDesc('published_at')
            ->paginate();

        return EventUpdateResource::collection($updates);
    }
}
