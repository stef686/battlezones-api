<?php

namespace App\Http\Resources\Users;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'updated_at' => $this->updated_at->toIso8601ZuluString(),
            'public_name' => $this->public_name,
            'country' => $this->country,
            $this->mergeWhen($request->user()?->is($this->resource), [
                'email' => $this->email,
            ]),
            'game_systems' => [],
            'avatar' => '',
            'location' => '',
            'events_count' => 0,
            'followers_count' => 0,
            'following_count' => 0,
        ];
    }
}
