<?php

namespace App\Services;

use App\Enums\PrivacyOption;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PrivacyService
{
    public function isBlocked(User $a, User $b): bool
    {
        return $a->blockedUsers()->where('blocked_id', $b->id)
            ->orWhere(function ($query) use ($a, $b) {
                $query->where('blocker_id', $b->id)->where('blocked_id', $a->id);
            })
            ->exists();
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
        return $target->followers()->where('follower_id', $actor->id)
            ->whereExists(function ($query) use ($actor, $target) {
                $query->from('follows')
                    ->where('follower_id', $target->id)
                    ->where('following_id', $actor->id);
            })
            ->exists();
    }

    /**
     * Stub: returns false until Club model exists.
     */
    public function areClubMembers(User $actor, User $target): bool
    {
        Log::warning('PrivacyService::areClubMembers() is not implemented — denying access by default.', [
            'actor_id' => $actor->id,
            'target_id' => $target->id,
        ]);

        return false;
    }

    /**
     * Stub: returns false until Event model exists.
     */
    public function isEventOrganiserOf(User $organiser, User $participant): bool
    {
        Log::warning('PrivacyService::isEventOrganiserOf() is not implemented — denying access by default.', [
            'organiser_id' => $organiser->id,
            'participant_id' => $participant->id,
        ]);

        return false;
    }
}
