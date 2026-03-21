<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gallery\UpdatePhotoRequest;
use App\Http\Resources\Gallery\PhotoResource;
use App\Models\Photo;
use App\Services\PhotoStorageService;
use Knuckles\Scribe\Attributes\Group;

#[Group('Gallery')]
class UpdatePhotoController extends Controller
{
    public function __invoke(UpdatePhotoRequest $request, Photo $photo, PhotoStorageService $storage): PhotoResource
    {
        $data = $request->safe()->except('photo');

        if ($request->hasFile('photo')) {
            $data = [
                ...$data,
                ...$storage->replace($photo, $request->file('photo'), $request->user()->id),
            ];
        }

        $photo->update($data);
        $photo->loadCount('reactions');

        return PhotoResource::make($photo);
    }
}
