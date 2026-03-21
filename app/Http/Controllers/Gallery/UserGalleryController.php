<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Gallery\PhotoResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Group;

#[Group('Gallery')]
class UserGalleryController extends Controller
{
    public function __invoke(Request $request, User $user): AnonymousResourceCollection
    {
        $photos = $user->photos()
            ->withReactionData($request->user()->id)
            ->latest()
            ->paginate();

        return PhotoResource::collection($photos);
    }
}
