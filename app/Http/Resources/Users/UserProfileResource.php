<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class UserProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'updated_at' => $this->updated_at->toIso8601ZuluString(),
            'public_name' => $this->public_name,
            'game_systems' => [],
            'avatar' => '',
            'location' => '',
            'events_count' => 0,
            'followers_count' => 0,
            'following_count' => 0,
        ];
    }
}
