<?php

namespace App\Http\Resources\Users;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $authFollowingIds = once(fn () => $request->user()
            ?->following()->pluck('users.id')->all() ?? []);

        return [
            'id' => $this->id,
            'public_name' => $this->public_name,
            'avatar' => '',
            'is_following' => in_array($this->id, $authFollowingIds),
        ];
    }
}
