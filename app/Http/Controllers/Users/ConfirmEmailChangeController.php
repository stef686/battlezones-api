<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\PendingEmailChange;
use App\Models\User;
use App\Services\Frontend;
use Illuminate\Http\RedirectResponse;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Users', 'APIs for Users')]
class ConfirmEmailChangeController extends Controller
{
    /**
     * The signature only validates on the host that generated it, so this stays
     * on the API domain and redirects to the SPA once the change has landed.
     */
    #[Endpoint('Confirm Email Change', 'Confirm a pending email change via the emailed verification link.')]
    #[UrlParam('user_id', 'integer', 'The ID of the user.', example: 1)]
    #[UrlParam('token', 'string', 'The verification token from the email.')]
    #[Response(status: 302, description: 'Redirects into the SPA carrying the outcome.')]
    public function __invoke(User $user, string $token): RedirectResponse
    {
        $pending = PendingEmailChange::query()
            ->where('user_id', $user->id)
            ->first();

        if (! $pending || ! hash_equals($pending->token, hash('sha256', $token))) {
            return redirect()->away(Frontend::resultUrl(Frontend::EMAIL_CHANGED_PATH, 'invalid'));
        }

        $user->forceFill([
            'email' => $pending->email,
            'email_verified_at' => now(),
        ])->save();

        $pending->delete();

        $user->tokens()->delete();

        return redirect()->away(Frontend::resultUrl(Frontend::EMAIL_CHANGED_PATH, 'changed'));
    }
}
