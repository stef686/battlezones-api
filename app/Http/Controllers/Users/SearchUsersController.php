<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\SearchUsersRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Users', 'APIs for Users')]
class SearchUsersController extends Controller
{
    #[Endpoint('Search Users', 'Search for users by username or name.')]
    #[Response(['data' => [['id' => 1, 'public_name' => 'John Doe', 'username' => 'johndoe']]])]
    public function __invoke(SearchUsersRequest $request): JsonResponse
    {
        $query = $request->validated('q');
        $authUser = $request->user();

        $users = User::query()
            ->where('id', '!=', $authUser->id)
            ->whereNotIn('id', $authUser->allBlockedIds())
            ->where(function ($q) use ($query) {
                $q->where('username', 'like', "{$query}%")
                    ->orWhere('name', 'like', "{$query}%");
            })
            ->limit(config('battlezones.search_result_limit'))
            ->get(['id', 'name', 'username', 'show_public_name']);

        return response()->json([
            'data' => $users->map(fn (User $user) => [
                'id' => $user->id,
                'public_name' => $user->public_name,
                'username' => $user->username,
            ])->values(),
        ]);
    }
}
