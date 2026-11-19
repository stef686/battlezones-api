<?php

namespace App\Actions\Events;

use App\Enums\EventInviteRole;
use App\Exceptions\PartnerListIsLocked;
use App\Models\EventAttendeeMembership;
use App\Models\EventInvite;
use App\Models\User;

/**
 * Corrects the details of a Player in an Attendee.
 *
 * A Captain names their partner from memory — a nickname, an address typed on
 * a phone — and has no way to fix either once it is sent unless somebody can
 * amend it on their behalf. Once the Player claims the account, their name and
 * address are theirs alone; their Faction belongs to their seat in the Event,
 * so an Organiser can still correct it. Who may send what is decided by the
 * caller before this runs.
 */
class AmendAttendeeMember
{
    public function __construct(
        private readonly SendEventInvite $sendEventInvite,
    ) {}

    /**
     * @param  array{name?: string|null, email?: string, faction_id?: int|null}  $changes
     * @param  bool  $factionLocksWithList  Whether a locked army list refuses the Faction change, as it does for a team mate but not an Organiser.
     *
     * @throws PartnerListIsLocked
     */
    public function handle(
        EventAttendeeMembership $membership,
        array $changes,
        User $amendedBy,
        bool $factionLocksWithList,
    ): EventAttendeeMembership {
        $player = $membership->user;

        if (array_key_exists('faction_id', $changes)) {
            $this->changeFaction($membership, $changes['faction_id'], $factionLocksWithList);
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
     * Write the Faction, conditional on the list still being unlocked where
     * the lock applies, so a list submitted at the same moment cannot have its
     * Faction changed under it.
     *
     * The Faction is written first, so a refusal leaves the name and address
     * untouched too. MySQL counts rows changed rather than matched, so zero
     * rows is only a refusal once the re-read row says the list is locked.
     *
     * @throws PartnerListIsLocked
     */
    private function changeFaction(EventAttendeeMembership $membership, ?int $factionId, bool $factionLocksWithList): void
    {
        if (! $factionLocksWithList) {
            $membership->update(['faction_id' => $factionId]);

            return;
        }

        $changed = EventAttendeeMembership::query()
            ->whereKey($membership->getKey())
            ->whereNull('army_list_submitted_at')
            ->update(['faction_id' => $factionId]);

        if ($changed === 0 && $membership->refresh()->isArmyListLocked()) {
            throw new PartnerListIsLocked();
        }
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
