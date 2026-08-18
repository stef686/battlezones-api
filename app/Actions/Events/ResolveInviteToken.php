<?php

namespace App\Actions\Events;

use App\Models\EventInvite;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Turns a plain invite token into the Invite it stands for.
 *
 * A dead token is answered with a machine-readable reason rather than a
 * generic failure, so the front end can offer "log in to continue" where that
 * is what the holder actually needs.
 */
class ResolveInviteToken
{
    public function handle(#[\SensitiveParameter] string $plainToken): EventInvite
    {
        $invite = EventInvite::findByToken($plainToken);

        if (! $invite instanceof EventInvite) {
            throw $this->reject('invite_not_found', 'This invitation link is not valid.', 404);
        }

        // Revoked comes first: claiming an account revokes its tokens, so this
        // is the holder who now has a real login rather than a dead end.
        if ($invite->isRevoked()) {
            throw $this->reject(
                'invite_revoked',
                'This invitation has been used to set up an account. Log in to continue.',
                410,
            );
        }

        if ($invite->hasExpired()) {
            throw $this->reject(
                'invite_expired',
                'This invitation has expired. Log in, or ask an organiser for a new one.',
                410,
            );
        }

        return $invite;
    }

    private function reject(string $code, string $message, int $status): HttpResponseException
    {
        return new HttpResponseException(response()->json([
            'message' => $message,
            'code' => $code,
        ], $status));
    }
}
