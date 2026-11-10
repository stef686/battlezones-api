<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\CreateEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\StoreEventRequest;
use App\Http\Resources\Events\EventResource;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class StoreEventController extends Controller
{
    public function __construct(private CreateEvent $createEvent) {}

    #[Endpoint('Create an Event', 'Claimed accounts only. Creates the Event as a Draft, with the caller as its Lead Organiser and the Score Types it is ranked on, in one go. Upload a Banner afterwards through the Banner endpoint.')]
    #[ResponseFromApiResource(EventResource::class, model: Event::class, status: 201)]
    public function __invoke(StoreEventRequest $request): JsonResponse
    {
        /** @var User $creator */
        $creator = $request->user();

        $event = $this->createEvent->execute($creator, $request->eventAttributes(), $request->scoreTypes());

        $event->load(['gameSystem', 'documents']);
        $event->loadCount('attendees');

        return EventResource::make($event)->withViewer()->response()->setStatusCode(201);
    }
}
