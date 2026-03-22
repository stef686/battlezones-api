<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\SearchUsersRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class SearchUsersController extends Controller
{
    public function __invoke(SearchUsersRequest $request): JsonResponse
    {
        $query = $request->validated('q');
        $authId = $request->user()->id;

        $authUser = $request->user();
        $blockedIds = $authUser->blockedUsers()->pluck('blocked_id')
            ->merge($authUser->blockedBy()->pluck('blocker_id'));

        $users = User::query()
            ->where('id', '!=', $authId)
            ->whereNotIn('id', $blockedIds)
            ->where(function ($q) use ($query) {
                $q->where('username', 'like', "{$query}%")
                    ->orWhere('name', 'like', "{$query}%");
            })
            ->limit(10)
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
