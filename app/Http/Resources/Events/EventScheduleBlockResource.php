<?php

namespace App\Http\Resources\Events;

use App\Models\EventScheduleBlock;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EventScheduleBlock
 */
class EventScheduleBlockResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'type' => $this->type->value,
            'starts_at' => $this->starts_at->toIso8601String(),
            'ends_at' => $this->ends_at->toIso8601String(),
            'display_order' => $this->display_order,
            'target_id' => $this->targetId(),
            'is_target_live' => $this->isTargetLive(),
            'round' => $this->round === null ? null : [
                'id' => $this->round->id,
                'number' => $this->round->number,
                'name' => $this->round->name,
            ],
        ];
    }
}
