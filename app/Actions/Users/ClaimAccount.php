<?php

namespace App\Actions\Users;

use App\Models\EventInvite;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Turns an invited account into a real one.
 *
 * Setting a password is the whole point of an Invite, so it also retires every
 * credential that stood in for one: from here on the person logs in as
 * themselves, and a forwarded invitation email is worth nothing.
 */
class ClaimAccount
{
    public function handle(User $user, #[\SensitiveParameter] string $password, ?string $name = null): User
    {
        $attributes = [
            'password' => Hash::make($password),
            'claimed_at' => now(),
            // Receiving the invitation proves the address as surely as a
            // verification email would.
            'email_verified_at' => $user->email_verified_at ?? now(),
        ];

        // An invited account carries the name an Organiser typed for it, so
        // claiming is the first chance its owner has to correct it.
        if ($name !== null && $name !== '') {
            $attributes['name'] = $name;
        }

        $user->forceFill($attributes)->save();

        EventInvite::query()
            ->where('user_id', $user->getKey())
            ->outstanding()
            ->update(['revoked_at' => now()]);

        return $user;
    }
}
