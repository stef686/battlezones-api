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
class UnblockUserController extends Controller
{
    #[Endpoint('Unblock User', 'Unblock the given user.')]
    #[ResponseFromApiResource(UserProfileResource::class, model: User::class)]
    public function __invoke(Request $request, User $user): JsonResponse
    {
        $request->user()->blockedUsers()->detach($user);

        $user->loadCount(['followers', 'following']);

        return response()->json([
            'message' => 'User unblocked',
        ]);
    }
}
