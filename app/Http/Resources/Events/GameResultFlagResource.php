<?php

namespace App\Http\Resources\Events;

use App\Models\EventAttendee;
use App\Models\GameResultFlag;
use App\Models\GameScore;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin GameResultFlag
 */
class GameResultFlagResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'game_id' => $this->game_id,
            'reason' => $this->reason,
            'flagged_at' => $this->created_at?->toIso8601String(),
            'flagged_by' => [
                'id' => $this->flaggedBy->id,
                'name' => $this->flaggedBy->name,
            ],
            'game' => $this->whenLoaded('game', fn (): array => [
                'id' => $this->game->id,
                'table_number' => $this->game->table_number,
                'is_bye' => $this->game->is_bye,
                'round' => [
                    'id' => $this->game->round->id,
                    'number' => $this->game->round->number,
                    'name' => $this->game->round->name,
                ],
                'attendees' => $this->game->attendees->map(fn (EventAttendee $attendee): array => [
                    'id' => $attendee->id,
                    'name' => $attendee->displayName(),
                    'scores' => $this->game->scores
                        ->where('event_attendee_id', $attendee->id)
                        ->mapWithKeys(fn (GameScore $score): array => [$score->scoreType->slug => $score->value])
                        ->all(),
                ])->values()->all(),
            ]),
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'resolved_by' => $this->whenLoaded('resolvedBy', fn (): ?array => $this->resolvedBy === null ? null : [
                'id' => $this->resolvedBy->id,
                'name' => $this->resolvedBy->name,
            ]),
        ];
    }
}
