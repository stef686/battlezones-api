<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\PendingPasswordChange;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ConfirmPasswordChangeController extends Controller
{
    public function __invoke(User $user, string $token): JsonResponse
    {
        $pending = PendingPasswordChange::query()
            ->where('user_id', $user->id)
            ->first();

        if (! $pending || ! hash_equals($pending->token, hash('sha256', $token))) {
            return response()->json(['message' => 'Invalid confirmation link.'], 403);
        }

        if ($pending->isExpired()) {
            $pending->delete();

            return response()->json([
                'message' => 'This confirmation link has expired. Please request a new password change. Your password has not been changed.',
            ], 410);
        }

        $user->forceFill(['password' => $pending->password])->save();

        $pending->delete();

        $user->tokens()->delete();

        return response()->json(['message' => 'Your password has been updated.']);
    }
}
