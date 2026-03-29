<?php

namespace App\Http\Controllers\Users;

use App\Actions\Users\BlockUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\BlockUserRequest;
use App\Http\Resources\Users\UserProfileResource;
use App\Models\User;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Users', 'APIs for Users')]
class BlockUserController extends Controller
{
    #[Endpoint('Block User', 'Block the given user.')]
    #[ResponseFromApiResource(UserProfileResource::class, model: User::class)]
    public function __invoke(BlockUserRequest $request, User $user, BlockUser $action): UserProfileResource
    {
        $action->execute($request->user(), $user);

        $user->loadCount(['followers', 'following']);

        return UserProfileResource::make($user);
    }
}
