<?php

namespace App\Http\Resources\Events;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class EventOrganiserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $organiser */
        $organiser = $this->resource;

        return [
            'id' => $organiser->id,
            'name' => $organiser->public_name,
            'role' => $organiser->getRelationValue('pivot')?->role,
        ];
    }
}
