<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\SearchUsersRequest;
use App\Http\Resources\Users\UserSearchResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Users', 'APIs for Users')]
class SearchUsersController extends Controller
{
    #[Endpoint('Search Users', 'Search for users by username or name.')]
    #[ResponseFromApiResource(UserSearchResource::class, model: User::class, collection: true)]
    public function __invoke(SearchUsersRequest $request): AnonymousResourceCollection
    {
        $query = $request->validated('q');
        $authUser = $request->user();

        $users = User::query()
            ->claimed()
            ->where('id', '!=', $authUser->id)
            ->whereNotIn('id', $authUser->allBlockedIds())
            ->where(function ($q) use ($query) {
                $q->where('username', 'like', "{$query}%")
                    ->orWhere('name', 'like', "{$query}%");
            })
            ->limit(config('battlezones.search_result_limit'))
            ->get();

        return UserSearchResource::collection($users);
    }
}
