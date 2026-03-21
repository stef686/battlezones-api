<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Knuckles\Scribe\Attributes\Group;

#[Group('Gallery')]
class DeletePhotoController extends Controller
{
    public function __invoke(Photo $photo): Response
    {
        Gate::authorize('delete', $photo);

        Storage::disk('public')->delete(array_filter([$photo->path, $photo->thumbnail_path]));
        $photo->delete();

        return response()->noContent();
    }
}
