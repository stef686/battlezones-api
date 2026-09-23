<?php

namespace App\Actions\Events;

use App\Enums\EventInviteRole;
use App\Models\EventAttendeeMembership;
use App\Models\EventInvite;
use App\Models\User;

/**
 * Corrects the details of a Player who has not answered their invitation.
 *
 * A Captain names their partner from memory — a nickname, an address typed on
 * a phone — and has no way to fix either once it is sent unless somebody can
 * amend it on their behalf. Once the Player claims the account it is theirs,
 * and this path is closed to everybody but the Player themselves.
 */
class AmendAttendeeMember
{
    public function __construct(
        private readonly SendEventInvite $sendEventInvite,
    ) {}

    /**
     * @param  array{name?: string|null, email?: string, faction_id?: int|null}  $changes
     */
    public function handle(EventAttendeeMembership $membership, array $changes, User $amendedBy): EventAttendeeMembership
    {
        $player = $membership->user;

        if (array_key_exists('faction_id', $changes)) {
            $membership->update(['faction_id' => $changes['faction_id']]);
        }

        if (array_key_exists('name', $changes) && $changes['name'] !== null) {
            $player->update(['name' => $changes['name']]);
        }

        $email = $changes['email'] ?? null;

        if ($email !== null && mb_strtolower($email) !== mb_strtolower($player->email)) {
            $this->reissueTo($membership, $email, $changes['name'] ?? $player->name, $amendedBy);
        }

        return $membership->fresh() ?? $membership;
    }

    /**
     * Move the membership to the account the corrected address belongs to.
     *
     * The membership row travels rather than being rebuilt, so the Faction and
     * army list already entered against this seat survive the correction. The
     * account left behind keeps nothing: its invitation is revoked, because a
     * credential emailed to a mistyped address must stop opening the Event.
     */
    private function reissueTo(EventAttendeeMembership $membership, string $email, ?string $name, User $amendedBy): void
    {
        $attendee = $membership->attendee;
        $abandoned = $membership->user;

        $invite = $this->sendEventInvite->handle(
            event: $attendee->event,
            email: $email,
            role: EventInviteRole::Player,
            name: $name,
            attendee: $attendee,
            invitedBy: $amendedBy,
        );

        if ($invite->user->getKey() === $abandoned->getKey()) {
            return;
        }

        $membership->update(['user_id' => $invite->user->getKey()]);

        EventInvite::query()
            ->where('event_id', $attendee->event_id)
            ->where('user_id', $abandoned->getKey())
            ->each(fn (EventInvite $stale) => $stale->revoke());
    }
}
