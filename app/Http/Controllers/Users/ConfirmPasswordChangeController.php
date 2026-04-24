<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\PendingPasswordChange;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Users', 'APIs for Users')]
class ConfirmPasswordChangeController extends Controller
{
    #[Endpoint('Confirm Password Change', 'Confirm a pending password change via the emailed confirmation link.')]
    #[UrlParam('user', 'integer', 'The ID of the user.', example: 1)]
    #[UrlParam('token', 'string', 'The confirmation token from the email.')]
    #[Response(['message' => 'Your password has been updated.'])]
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
