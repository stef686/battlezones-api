<?php

namespace App\Http\Resources\Users;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'updated_at' => $this->updated_at->toIso8601ZuluString(),
            'public_name' => $this->public_name,
            'country' => $this->country,
            // Private to the reader: the SPA's restricted-mode guard reads
            // `is_claimed` as a field rather than inferring it from an absence.
            $this->mergeWhen($request->user()?->is($this->resource), fn (): array => [
                'email' => $this->email,
                'is_claimed' => $this->isClaimed(),
                'email_verified' => $this->hasVerifiedEmail(),
                'unread_notifications_count' => $this->unreadNotifications()->count(),
            ]),
            'game_systems' => [],
            'avatar' => '',
            'location' => '',
            'events_count' => 0,
            'followers_count' => $this->followers_count ?? 0,
            'following_count' => $this->following_count ?? 0,
            $this->mergeWhen($request->user()?->isNot($this->resource), fn () => [
                'is_following' => (bool) $request->user()?->following()->where('following_id', $this->id)->exists(),
                'is_blocked_by_you' => (bool) $request->user()?->blockedUsers()->where('blocked_id', $this->id)->exists(),
            ]),
        ];
    }
}
