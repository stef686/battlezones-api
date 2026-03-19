<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\PendingEmailChange;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ConfirmEmailChangeController extends Controller
{
    public function __invoke(User $user, string $token): JsonResponse
    {
        $pending = PendingEmailChange::query()
            ->where('user_id', $user->id)
            ->first();

        if (! $pending || ! hash_equals($pending->token, hash('sha256', $token))) {
            return response()->json(['message' => 'Invalid verification link.'], 403);
        }

        $user->forceFill([
            'email' => $pending->email,
            'email_verified_at' => now(),
        ])->save();

        $pending->delete();

        $user->tokens()->delete();

        return response()->json(['message' => 'Your email address has been updated.']);
    }
}
