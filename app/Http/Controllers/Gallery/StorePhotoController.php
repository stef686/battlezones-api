<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gallery\StorePhotoRequest;
use App\Http\Resources\Gallery\PhotoResource;
use App\Services\PhotoStorageService;
use Knuckles\Scribe\Attributes\Group;

#[Group('Gallery')]
class StorePhotoController extends Controller
{
    public function __invoke(StorePhotoRequest $request, PhotoStorageService $storage): PhotoResource
    {
        $user = $request->user();
        $paths = $storage->store($request->file('photo'), $user->id);

        $photo = $user->photos()->create([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            ...$paths,
        ]);

        $photo->loadCount('reactions');

        return PhotoResource::make($photo);
    }
}
