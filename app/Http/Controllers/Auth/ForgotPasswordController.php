<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Authentication', 'APIs for authentication')]
class ForgotPasswordController extends Controller
{
    #[Endpoint('Forgot Password', 'Sends a password reset link to the given email address')]
    #[BodyParam('email', 'string', 'The email address associated with the account.', required: true, example: 'user@example.com')]
    #[Response(['message' => 'If a user with that email address exists, we have sent a password reset link. Please check your email.'])]
    public function __invoke(ForgotPasswordRequest $request): JsonResponse
    {
        Password::sendResetLink($request->only('email'));

        return response()->json([
            'message' => 'If a user with that email address exists, we have sent a password reset link. Please check your email.',
        ]);
    }
}
