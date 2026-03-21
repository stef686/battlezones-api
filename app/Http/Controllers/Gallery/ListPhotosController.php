<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Gallery\PhotoResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Group;

#[Group('Gallery')]
class ListPhotosController extends Controller
{
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
