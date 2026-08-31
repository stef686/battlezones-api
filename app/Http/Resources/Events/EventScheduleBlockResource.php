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
            'target_state' => $this->targetState()?->value,
            'round' => $this->round === null ? null : [
                'id' => $this->round->id,
                'number' => $this->round->number,
                'name' => $this->round->name,
                // A Draft Round is not there for anybody but an Organiser, so
                // the schedule has to know whether the block it draws is one a
                // reader can open. Without it the row is a link to a 404 for
                // every Player until the Round is published.
                'status' => $this->round->status->value,
            ],
        ];
    }
}
