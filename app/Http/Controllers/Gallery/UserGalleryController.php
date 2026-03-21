<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Gallery\PhotoResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Group;

#[Group('Gallery')]
class UserGalleryController extends Controller
{
    public function __invoke(User $user): AnonymousResourceCollection
    {
        $photos = $user->photos()
            ->withCount('reactions')
            ->latest()
            ->paginate();

        return PhotoResource::collection($photos);
    }
}
