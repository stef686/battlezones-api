<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Gallery\PhotoResource;
use App\Models\Event;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
class ListEventGalleryController extends Controller
{
    #[Endpoint('List Event Gallery', 'Paginated photos for a publicly visible event.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[ResponseFromApiResource(PhotoResource::class, model: Photo::class, paginate: 15)]
    public function __invoke(Request $request, Event $event): AnonymousResourceCollection
    {
        abort_unless($event->status->isPubliclyVisible(), 404);

        $user = $request->user('sanctum');

        $photos = $event->photos()
            ->when($user, fn ($query) => $query->withReactionData($user->id), fn ($query) => $query->withCount('reactions'))
            ->latest()
            ->paginate();

        return PhotoResource::collection($photos);
    }
}
