<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\EventResource;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

/**
 * What the viewer runs, as opposed to what anyone may see: the public
 * listing leaves out Drafts and Cancelled Events by design, and this one is
 * the only way back to a Draft once its Organiser has left the wizard.
 */
#[Group('Events', 'APIs for Events')]
#[Authenticated]
class ListOrganisedEventsController extends Controller
{
    #[Endpoint('List the Events I Organise', 'Every Event the viewer is an Organiser of, lead or co-Organiser, in every status including Draft and Cancelled. Ordered by start, not paginated, and with no viewer block.')]
    #[ResponseFromApiResource(EventResource::class, model: Event::class, collection: true)]
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        $events = $user->organisedEvents()
            ->with('gameSystem')
            // `is_full` on every row would otherwise be a count per Event.
            ->withCount('attendees')
            ->orderBy('starts_at')
            ->get();

        return EventResource::collection($events);
    }
}
