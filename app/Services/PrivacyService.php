<?php

namespace App\Services;

use App\Enums\PrivacyOption;
use App\Models\User;

class PrivacyService
{
    public function isBlocked(User $a, User $b): bool
    {
        return $a->hasBlocked($b) || $b->hasBlocked($a);
    }

    public function canMessage(User $sender, User $recipient): bool
    {
        if ($this->isBlocked($sender, $recipient)) {
            return false;
        }

        return $this->satisfiesOption($sender, $recipient, $recipient->getMessagingPrivacy());
    }

    public function canViewProfile(User $viewer, User $target): bool
    {
        if ($viewer->id === $target->id) {
            return true;
        }

        if ($viewer->isBlockedBy($target)) {
            return false;
        }

        return $this->satisfiesOption($viewer, $target, $target->getProfilePrivacy());
    }

    public function satisfiesOption(User $actor, User $target, PrivacyOption $option): bool
    {
        return match ($option) {
            PrivacyOption::Anyone => true,
            PrivacyOption::FollowersOnly => $target->followers()->where('follower_id', $actor->id)->exists(),
            PrivacyOption::FollowingOnly => $target->following()->where('following_id', $actor->id)->exists(),
            PrivacyOption::MutualFollowers => $this->areMutualFollowers($actor, $target),
            PrivacyOption::FellowClubMembers => $this->areClubMembers($actor, $target),
        };
    }

    private function areMutualFollowers(User $actor, User $target): bool
    {
        return $target->followers()->where('follower_id', $actor->id)->exists()
            && $target->following()->where('following_id', $actor->id)->exists();
    }

    /**
     * Stub: returns false until Club model exists.
     */
    public function areClubMembers(User $actor, User $target): bool
    {
        return false;
    }

    /**
     * Stub: returns false until Event model exists.
     */
    public function isEventOrganiserOf(User $organiser, User $participant): bool
    {
        return false;
    }
}
