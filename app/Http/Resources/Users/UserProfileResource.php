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
            'username' => $this->username,
            'country' => $this->country,
            $this->mergeWhen($request->user()?->is($this->resource), [
                'email' => $this->email,
                'show_public_name' => $this->show_public_name,
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
