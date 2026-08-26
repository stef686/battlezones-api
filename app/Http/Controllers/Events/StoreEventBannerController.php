<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\StoreEventBannerRequest;
use App\Http\Resources\Events\EventResource;
use App\Models\Event;
use App\Services\EventBannerService;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class StoreEventBannerController extends Controller
{
    public function __construct(private EventBannerService $banners) {}

    #[Endpoint('Upload an Event Banner', 'Organisers only. A multipart route of its own rather than a field on the Event PATCH, because PHP does not populate uploaded files for PATCH bodies. The upload is scaled and cropped to 1600x534 and 800x267 WebP and the original is discarded, so re-framing later means uploading again.')]
    #[UrlParam('event_slug', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[ResponseFromApiResource(EventResource::class, model: Event::class)]
    public function __invoke(StoreEventBannerRequest $request, Event $event): EventResource
    {
        $this->banners->replace($event, $request->file('banner'));

        return EventResource::make($event->fresh(['gameSystem', 'documents']))->withViewer();
    }
}
