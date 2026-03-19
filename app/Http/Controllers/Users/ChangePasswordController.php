<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\ChangePasswordRequest;
use App\Models\PendingPasswordChange;
use App\Notifications\Profile\ConfirmPasswordChangeNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ChangePasswordController extends Controller
{
    public function __invoke(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        PendingPasswordChange::query()->where('user_id', $user->id)->delete();

        $token = Str::random(64);

        PendingPasswordChange::query()->create([
            'user_id' => $user->id,
            'password' => Hash::make($request->validated('password')),
            'token' => hash('sha256', $token),
            'created_at' => now(),
        ]);

        $user->notify(new ConfirmPasswordChangeNotification($token));

        return response()->json([
            'message' => 'A confirmation link has been sent to your email address.',
        ]);
    }
}
