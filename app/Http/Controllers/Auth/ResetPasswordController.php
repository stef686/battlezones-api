<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Authentication', 'APIs for authentication')]
class ResetPasswordController extends Controller
{
    #[Endpoint('Reset Password', 'Resets the user\'s password using a valid reset token')]
    #[BodyParam('token', 'string', 'The password reset token from the reset email.', required: true)]
    #[BodyParam('email', 'string', 'The email address associated with the account.', required: true, example: 'user@example.com')]
    #[BodyParam('password', 'string', 'The new password (minimum 8 characters).', required: true, example: 'new-password')]
    #[BodyParam('password_confirmation', 'string', 'Confirmation of the new password.', required: true, example: 'new-password')]
    #[Response(['message' => 'Your password has been reset. You may now log in with your new password.'])]
    #[Response(content: ['message' => 'This password reset token is invalid or has expired.'], status: 422)]
    public function __invoke(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => bcrypt($password)])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => 'Your password has been reset. You may now log in with your new password.',
        ]);
    }
}
