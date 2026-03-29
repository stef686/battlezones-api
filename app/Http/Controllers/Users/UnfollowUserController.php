<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\UserProfileResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Users', 'APIs for Users')]
class UnfollowUserController extends Controller
{
    #[Endpoint('Unfollow User', 'Unfollow the given user.')]
    #[ResponseFromApiResource(UserProfileResource::class, model: User::class)]
    public function __invoke(Request $request, User $user): JsonResponse
    {
        $request->user()->following()->detach($user);

        $user->loadCount(['followers', 'following']);

        return response()->json([
            'message' => 'User unfollowed',
        ]);
    }
}
