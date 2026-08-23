<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\PendingPasswordChange;
use App\Models\User;
use App\Services\Frontend;
use Illuminate\Http\RedirectResponse;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Users', 'APIs for Users')]
class ConfirmPasswordChangeController extends Controller
{
    /**
     * The signature only validates on the host that generated it, so this stays
     * on the API domain and redirects to the SPA once the change has landed.
     */
    #[Endpoint('Confirm Password Change', 'Confirm a pending password change via the emailed confirmation link.')]
    #[UrlParam('user_id', 'integer', 'The ID of the user.', example: 1)]
    #[UrlParam('token', 'string', 'The confirmation token from the email.')]
    #[Response(status: 302, description: 'Redirects into the SPA carrying the outcome.')]
    public function __invoke(User $user, string $token): RedirectResponse
    {
        $pending = PendingPasswordChange::query()
            ->where('user_id', $user->id)
            ->first();

        if (! $pending || ! hash_equals($pending->token, hash('sha256', $token))) {
            return redirect()->away(Frontend::resultUrl(Frontend::PASSWORD_CHANGED_PATH, 'invalid'));
        }

        if ($pending->isExpired()) {
            $pending->delete();

            return redirect()->away(Frontend::resultUrl(Frontend::PASSWORD_CHANGED_PATH, 'expired'));
        }

        $user->forceFill(['password' => $pending->password])->save();

        $pending->delete();

        $user->tokens()->delete();

        return redirect()->away(Frontend::resultUrl(Frontend::PASSWORD_CHANGED_PATH, 'changed'));
    }
}
