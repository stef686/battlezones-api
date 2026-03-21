<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gallery\StorePhotoRequest;
use App\Http\Resources\Gallery\PhotoResource;
use App\Services\ThumbnailService;
use Illuminate\Support\Str;
use Knuckles\Scribe\Attributes\Group;

#[Group('Gallery')]
class StorePhotoController extends Controller
{
    public function __invoke(StorePhotoRequest $request, ThumbnailService $thumbnailService): PhotoResource
    {
        $user = $request->user();
        $file = $request->file('photo');
        $extension = $file->getClientOriginalExtension();
        $path = $file->storeAs(
            "photos/{$user->id}",
            Str::uuid().".{$extension}",
            'public',
        );

        $thumbnailPath = $thumbnailService->generate($file, $user->id);

        $photo = $user->photos()->create([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'path' => $path,
            'thumbnail_path' => $thumbnailPath,
        ]);

        $photo->loadCount('reactions');

        return PhotoResource::make($photo);
    }
}
