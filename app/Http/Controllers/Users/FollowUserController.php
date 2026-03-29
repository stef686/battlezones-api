<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\FollowUserRequest;
use App\Http\Resources\Users\UserProfileResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Users', 'APIs for Users')]
class FollowUserController extends Controller
{
    #[Endpoint('Follow User', 'Follow the given user.')]
    #[ResponseFromApiResource(UserProfileResource::class, model: User::class)]
    public function __invoke(FollowUserRequest $request, User $user): JsonResponse
    {
        $request->user()->following()->syncWithoutDetaching($user);

        $user->loadCount(['followers', 'following']);

        return response()->json([
            'message' => 'User followed',
        ]);
    }
}
