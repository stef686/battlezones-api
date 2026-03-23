<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\UserCardResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Users', 'APIs for Users')]
class ListBlockedUsersController extends Controller
{
    #[Endpoint('List Blocked Users', 'List the authenticated user\'s blocked users.')]
    #[ResponseFromApiResource(UserCardResource::class, model: User::class, paginate: 15)]
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $blockedUsers = $request->user()->blockedUsers()->paginate();

        return UserCardResource::collection($blockedUsers);
    }
}
