<?php

namespace App\Enums;

enum PrivacyOption: string
{
    case Anyone = 'anyone';
    case FollowersOnly = 'followers_only';
    case FollowingOnly = 'following_only';
    case MutualFollowers = 'mutual_followers';
    case FellowClubMembers = 'fellow_club_members';

    public function label(): string
    {
        return match ($this) {
            self::Anyone => 'Anyone',
            self::FollowersOnly => 'Followers Only',
            self::FollowingOnly => 'Following Only',
            self::MutualFollowers => 'Mutual Followers',
            self::FellowClubMembers => 'Fellow Club Members',
        };
    }
}
