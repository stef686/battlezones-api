<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Gallery\PhotoResource;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Gallery', 'APIs for Gallery')]
class ListPhotosController extends Controller
{
    #[Endpoint('List Photos', 'List the authenticated user\'s photos.')]
    #[ResponseFromApiResource(PhotoResource::class, model: Photo::class, paginate: 15)]
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $photos = $request->user()
            ->photos()
            ->withReactionData($request->user()->id)
            ->latest()
            ->paginate();

        return PhotoResource::collection($photos);
    }
}
