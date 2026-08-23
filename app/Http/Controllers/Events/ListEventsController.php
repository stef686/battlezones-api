<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\ListEventsRequest;
use App\Http\Resources\Events\EventResource;
use App\Models\Event;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Events', 'APIs for Events')]
class ListEventsController extends Controller
{
    #[Endpoint('List Events', 'List publicly visible events with optional filters and search.')]
    #[ResponseFromApiResource(EventResource::class, model: Event::class, paginate: 15)]
    public function __invoke(ListEventsRequest $request): AnonymousResourceCollection
    {
        $events = Event::query()
            ->publiclyVisible()
            ->with('gameSystem')
            // One subquery for the whole page: `is_full` on every row would
            // otherwise be a count per Event.
            ->withCount('attendees')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when(
                $request->filled('game_system'),
                fn ($query) => $query->whereHas('gameSystem', fn ($q) => $q->where('slug', $request->string('game_system'))),
            )
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('starts_at')
            ->paginate();

        return EventResource::collection($events);
    }
}
