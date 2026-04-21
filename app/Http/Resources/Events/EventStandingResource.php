<?php

namespace App\Http\Resources\Events;

use App\Models\EventStanding;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EventStanding
 */
class EventStandingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'position' => $this->position,
            'attendee' => [
                'id' => $this->attendee->id,
                'name' => $this->attendee->user->public_name,
                'faction' => $this->attendee->faction ? [
                    'id' => $this->attendee->faction->id,
                    'name' => $this->attendee->faction->name,
                ] : null,
                'clubs' => $this->attendee->user->clubs->map(fn ($club) => [
                    'id' => $club->id,
                    'name' => $club->name,
                ])->values(),
            ],
            'scores' => $this->scores->map(fn ($score) => [
                'value' => $score->value,
                'score_type' => [
                    'id' => $score->scoreType->id,
                    'name' => $score->scoreType->name,
                    'slug' => $score->scoreType->slug,
                    'sort_direction' => $score->scoreType->sort_direction->value,
                ],
            ])->values(),
        ];
    }
}
