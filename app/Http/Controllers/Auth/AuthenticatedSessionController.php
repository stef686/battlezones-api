<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class AuthenticatedSessionController extends Controller
{
    /**
     * Login
     *
     * Returns an authorization token for the user
     *
     * @group Authentication
     *
     * @subgroup Login
     *
     * @bodyParam device_name string required The name of the device logging in. Example: iPhone
     *
     * @response 200 scenario="User logged in" {"token": "{AUTH_TOKEN}"}
     *
     * @throws ValidationException
     */
    public function token(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

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

        return response()->json([
            'token' => $user->createToken($request->device_name)->plainTextToken,
        ]);
    }
}
