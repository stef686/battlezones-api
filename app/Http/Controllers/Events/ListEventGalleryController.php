<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Gallery\PhotoResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListEventGalleryController extends Controller
{
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
