<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gallery\UpdatePhotoRequest;
use App\Http\Resources\Gallery\PhotoResource;
use App\Models\Photo;
use App\Services\PhotoStorageService;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Gallery', 'APIs for Gallery')]
class UpdatePhotoController extends Controller
{
    #[Endpoint('Update Photo', 'Update a photo\'s name, description, or image.')]
    #[ResponseFromApiResource(PhotoResource::class, model: Photo::class)]
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
