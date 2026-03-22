<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\UserCardResource;
use App\Models\User;
use App\Services\PrivacyService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Users', 'APIs for Users')]
class ListFollowingController extends Controller
{
    #[Endpoint('List Following', 'List the users that the given user is following.')]
    #[ResponseFromApiResource(UserCardResource::class, paginate: 15)]
    public function __invoke(User $user, PrivacyService $privacyService): AnonymousResourceCollection
    {
        abort_unless($privacyService->canViewProfile(auth()->user(), $user), 403);

        $following = $user->following()->whereNotIn('users.id', auth()->user()->allBlockedIds())->paginate();

        return UserCardResource::collection($following);
    }
}
