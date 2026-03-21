<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gallery\DeletePhotoRequest;
use App\Models\Photo;
use App\Services\PhotoStorageService;
use Illuminate\Http\Response;
use Knuckles\Scribe\Attributes\Group;

#[Group('Gallery')]
class DeletePhotoController extends Controller
{
    public function __invoke(DeletePhotoRequest $request, Photo $photo, PhotoStorageService $storage): Response
    {
        $storage->delete($photo);
        $photo->delete();

        return response()->noContent();
    }
}
