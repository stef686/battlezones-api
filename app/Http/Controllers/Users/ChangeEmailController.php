<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\ChangeEmailRequest;
use App\Models\PendingEmailChange;
use App\Notifications\Profile\EmailChangeRequestedNotification;
use App\Notifications\Profile\VerifyNewEmailNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Users', 'APIs for Users')]
class ChangeEmailController extends Controller
{
    #[Endpoint('Change Email', 'Initiate change email address for a user')]
    #[Response(['message' => 'A verification link has been sent to your new email address.'])]
    public function __invoke(ChangeEmailRequest $request): JsonResponse
    {
        $user = $request->user();
        $newEmail = $request->validated('email');

        PendingEmailChange::query()->where('user_id', $user->id)->delete();

        $token = Str::random(64);

        PendingEmailChange::query()->create([
            'user_id' => $user->id,
            'email' => $newEmail,
            'token' => hash('sha256', $token),
            'created_at' => now(),
        ]);

        Notification::route('mail', $newEmail)
            ->notify(new VerifyNewEmailNotification($user, $token));

        $user->notify(new EmailChangeRequestedNotification($newEmail));

        return response()->json([
            'message' => 'A verification link has been sent to your new email address.',
        ]);
    }
}
