<?php

namespace App\Http\Resources\Events;

use App\Models\EventAttendeeMembership;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EventAttendeeMembership
 */
class ArmyListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var EventAttendeeMembership $membership */
        $membership = $this->resource;

        return [
            'army_list' => $membership->army_list,
            'submitted_at' => $membership->army_list_submitted_at,
            'is_locked' => $membership->isArmyListLocked(),
        ];
    }
}
