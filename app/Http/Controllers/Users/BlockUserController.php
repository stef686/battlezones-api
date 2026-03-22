<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\BlockUserRequest;
use App\Http\Resources\Users\UserProfileResource;
use App\Models\Conversation;
use App\Models\User;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Users', 'APIs for Users')]
class BlockUserController extends Controller
{
    #[Endpoint('Block User', 'Block the given user.')]
    #[ResponseFromApiResource(UserProfileResource::class)]
    public function __invoke(BlockUserRequest $request, User $user): UserProfileResource
    {
        $authUser = $request->user();

        $authUser->blockedUsers()->syncWithoutDetaching($user);

        $authUser->following()->detach($user);
        $authUser->followers()->detach($user);

        $conversation = Conversation::findBetween($authUser->id, $user->id);

        if ($conversation) {
            $conversation->users()->updateExistingPivot(
                [$authUser->id, $user->id],
                ['deleted_at' => now()],
            );
        }

        $user->loadCount(['followers', 'following']);

        return UserProfileResource::make($user);
    }
}
