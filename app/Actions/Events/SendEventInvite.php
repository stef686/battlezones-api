<?php

namespace App\Actions\Events;

use App\Enums\EventInviteRole;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventInvite;
use App\Models\User;
use App\Notifications\Events\EventInviteNotification;
use App\Notifications\Events\ExistingAccountInvitedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * The single write path for Invites, covering both roles.
 *
 * The User row is created when the invite is sent rather than on accept, so
 * the foreign key is always real and an Attendee can be registered against a
 * person who has not yet followed their link.
 */
class SendEventInvite
{
    public function handle(
        Event $event,
        string $email,
        EventInviteRole $role,
        ?string $name = null,
        ?EventAttendee $attendee = null,
        ?User $invitedBy = null,
    ): EventInvite {
        $user = $this->findOrCreateAccount($email, $name);

        // A claimed account already has a way in of its owner's choosing, so
        // it is told about the invitation rather than handed a credential.
        $plainToken = $user->isClaimed() ? null : Str::random(64);

        $invite = EventInvite::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => $user->id],
            [
                'event_attendee_id' => $attendee?->id,
                'invited_by_id' => $invitedBy?->id,
                'role' => $role,
                'token' => $plainToken === null ? null : EventInvite::hashToken($plainToken),
                'expires_at' => $this->expiryFor($event),
                'revoked_at' => null,
            ],
        );

        $user->notify($plainToken === null
            ? new ExistingAccountInvitedNotification($event, $role)
            : new EventInviteNotification($event, $role, $plainToken));

        return $invite;
    }

    private function findOrCreateAccount(string $email, ?string $name): User
    {
        $existing = User::where('email', $email)->first();

        if ($existing instanceof User) {
            return $existing;
        }

        return User::create([
            'name' => $name ?? Str::before($email, '@'),
            'email' => $email,
            'password' => null,
            'claimed_at' => null,
        ]);
    }

    /**
     * Lifetime runs from the end of the Event, not the registration deadline,
     * so a Captain who registers late still gets in on the day.
     */
    private function expiryFor(Event $event): Carbon
    {
        return $event->ends_at->copy()->addDays(config('battlezones.invite_expiry_days_after_event'));
    }
}
