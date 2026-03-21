<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gallery\UpdatePhotoRequest;
use App\Http\Resources\Gallery\PhotoResource;
use App\Models\Photo;
use App\Services\ThumbnailService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Knuckles\Scribe\Attributes\Group;

#[Group('Gallery')]
class UpdatePhotoController extends Controller
{
    public function __invoke(UpdatePhotoRequest $request, Photo $photo, ThumbnailService $thumbnailService): PhotoResource
    {
        $data = $request->safe()->except('photo');

        if ($request->hasFile('photo')) {
            Storage::disk('public')->delete(array_filter([$photo->path, $photo->thumbnail_path]));

            $user = $request->user();
            $file = $request->file('photo');
            $extension = $file->getClientOriginalExtension();

            $data['path'] = $file->storeAs(
                "photos/{$user->id}",
                Str::uuid().".{$extension}",
                'public',
            );
            $data['thumbnail_path'] = $thumbnailService->generate($file, $user->id);
        }

        $photo->update($data);
        $photo->loadCount('reactions');

        return PhotoResource::make($photo);
    }
}
