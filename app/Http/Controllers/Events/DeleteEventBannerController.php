<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Events\EventResource;
use App\Models\Event;
use App\Services\EventBannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class DeleteEventBannerController extends Controller
{
    public function __construct(private EventBannerService $banners) {}

    #[Endpoint('Remove an Event Banner', 'Organisers only. Deletes both stored variants and returns the header to its flat surface.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[ResponseFromApiResource(EventResource::class, model: Event::class)]
    public function __invoke(Request $request, Event $event): EventResource
    {
        Gate::authorize('organise', $event);

        $this->banners->delete($event);

        return EventResource::make($event->fresh(['gameSystem', 'documents']))->withViewer();
    }
}
