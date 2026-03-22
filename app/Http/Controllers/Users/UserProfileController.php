<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\UserProfileResource;
use App\Models\User;
use App\Services\PrivacyService;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Users', 'APIs for Users')]
class UserProfileController extends Controller
{
    #[Endpoint('User Profile', "Display the given user's profile data.")]
    #[ResponseFromApiResource(UserProfileResource::class)]
    public function __invoke(User $user, PrivacyService $privacyService): UserProfileResource
    {
        abort_unless($privacyService->canViewProfile(auth()->user(), $user), 403);

        $user->loadCount(['followers', 'following']);

        return UserProfileResource::make($user);
    }
}
