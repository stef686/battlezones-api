<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginTokenRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

#[Group('Authentication', 'APIs for authentication')]
class LoginTokenController extends Controller
{
    #[Endpoint('Login', 'Returns an authorization token for the user')]
    #[BodyParam('device_name', 'string', 'The name of the device logging in.', required: true, example: 'iPhone')]
    #[Response(['token' => '{AUTH_TOKEN}'])]
    #[Response(content: ['email' => ['The provided credentials are incorrect.']], status: 422)]
    public function __invoke(LoginTokenRequest $request): JsonResponse
    {

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (EnsureFrontendRequestsAreStateful::fromFrontend($request)) {
            Auth::login($user);

            return response()->json(['token' => null]);
        }

        $newToken = $user->createToken($request->input('device_name'));

        return response()->json([
            'token' => $newToken->plainTextToken,
            'expires_at' => $newToken->accessToken->created_at
                ->addMinutes(config('sanctum.expiration')),
        ]);
    }
}
