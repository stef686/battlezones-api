<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Gallery\PhotoResource;
use App\Models\Photo;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;

#[Group('Gallery')]
class ShowPhotoController extends Controller
{
    public function __invoke(Request $request, Photo $photo): PhotoResource
    {
        $photo->loadReactionData($request->user()->id);

        return PhotoResource::make($photo);
    }
}
