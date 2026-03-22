<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Gallery\PhotoResource;
use App\Models\User;
use App\Services\PrivacyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Group;

#[Group('Gallery')]
class UserGalleryController extends Controller
{
    public function __invoke(Request $request, User $user, PrivacyService $privacyService): AnonymousResourceCollection
    {
        abort_unless($privacyService->canViewProfile($request->user(), $user), 403);

        $photos = $user->photos()
            ->withReactionData($request->user()->id)
            ->latest()
            ->paginate();

        return PhotoResource::collection($photos);
    }
}
