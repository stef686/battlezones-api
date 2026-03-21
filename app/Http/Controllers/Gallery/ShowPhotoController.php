<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Gallery\PhotoResource;
use App\Models\Photo;
use Knuckles\Scribe\Attributes\Group;

#[Group('Gallery')]
class ShowPhotoController extends Controller
{
    public function __invoke(Photo $photo): PhotoResource
    {
        $photo->loadCount('reactions');

        return PhotoResource::make($photo);
    }
}
