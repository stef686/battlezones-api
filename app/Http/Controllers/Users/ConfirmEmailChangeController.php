<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\PendingEmailChange;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Users', 'APIs for Users')]
class ConfirmEmailChangeController extends Controller
{
    #[Endpoint('Confirm Email Change', 'Confirm a pending email change via the emailed verification link.')]
    #[UrlParam('user_id', 'integer', 'The ID of the user.', example: 1)]
    #[UrlParam('token', 'string', 'The verification token from the email.')]
    #[Response(['message' => 'Your email address has been updated.'])]
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
