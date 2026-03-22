<?php

namespace App\Http\Resources\Users;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserCardResource extends JsonResource
{
    /**
     * @var list<int>|null
     */
    private static ?array $authFollowingIds = null;

    public function toArray($request): array
    {
        if (self::$authFollowingIds === null) {
            self::$authFollowingIds = $request->user()
                ?->following()->pluck('users.id')->all() ?? [];
        }

        return [
            'id' => $this->id,
            'public_name' => $this->public_name,
            'avatar' => '',
            'is_following' => in_array($this->id, self::$authFollowingIds),
        ];
    }

    public static function resetAuthFollowing(): void
    {
        self::$authFollowingIds = null;
    }
}
