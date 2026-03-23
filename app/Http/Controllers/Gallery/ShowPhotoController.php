<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Gallery\PhotoResource;
use App\Models\Photo;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Gallery', 'APIs for Gallery')]
class ShowPhotoController extends Controller
{
    #[Endpoint('Show Photo', 'Get a specific photo by ID.')]
    #[ResponseFromApiResource(PhotoResource::class, model: Photo::class)]
    public function __invoke(Request $request, Photo $photo): PhotoResource
    {
        $photo->loadReactionData($request->user()->id);

        return PhotoResource::make($photo);
    }
}
