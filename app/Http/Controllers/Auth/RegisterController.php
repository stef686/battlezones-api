<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\Frontend;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

#[Group('Authentication', 'APIs for authentication')]
class RegisterController extends Controller
{
    #[Endpoint('Register', 'Registers a new user')]
    #[BodyParam('password', 'string', 'The new user\'s desired password.', required: true, example: 'password')]
    #[BodyParam('password_confirmation', 'string', 'Confirmation of the password field.', required: true, example: 'password')]
    #[BodyParam('device_name', 'string', 'The name of the device logging in.', required: true, example: 'iPhone')]
    #[Response(['token' => '{AUTH_TOKEN}'])]
    public function __invoke(RegisterRequest $request): JsonResponse
    {

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        $isStateful = EnsureFrontendRequestsAreStateful::fromFrontend($request);

        if ($isStateful) {
            Auth::login($user);
        }

        event(new Registered($user));

        return response()->json([
            'token' => $user->createToken($request->input('device_name'))->plainTextToken,
        ], 201);
    }

    #[Endpoint('Resend verification email', 'Resend verification email')]
    #[BodyParam('email', 'string', 'The new user\'s email address to send the verification email to.', required: true, example: 'test@test.com')]
    #[Response(['message' => 'Verification link resent!'])]
    #[Response(['message' => 'User not found.'], 404)]
    #[Response(['message' => 'Email already verified.'], 400)]
    public function resendVerification(Request $request): JsonResponse
    {
        $user = User::where('email', $request->input('email'))->first();

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link resent!']);
    }

    /**
     * The signature only validates on the host that generated it, so this stays
     * on the API domain, does its work, and hands the reader back to the SPA
     * with the outcome rather than answering them with JSON.
     */
    public function verifyEmail(User $id, string $hash): RedirectResponse
    {
        if (! hash_equals((string) $hash, sha1($id->getEmailForVerification()))) {
            return redirect()->away(Frontend::resultUrl(Frontend::EMAIL_VERIFIED_PATH, 'invalid'));
        }

        if (! $id->hasVerifiedEmail()) {
            $id->markEmailAsVerified();
        }

        return redirect()->away(Frontend::resultUrl(Frontend::EMAIL_VERIFIED_PATH, 'verified'));
    }
}
