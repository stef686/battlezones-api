<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SendVerificationEmail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
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
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'device_name' => 'required',
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        $isStateful = EnsureFrontendRequestsAreStateful::fromFrontend($request);

        if ($isStateful) {
            Auth::login($user);
        }

        SendVerificationEmail::dispatch($user);

        return response()->json([
            'token' => $user->createToken($request->input('device_name'))->plainTextToken,
        ], 201);
    }
}
