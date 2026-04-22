<?php

namespace App\Http\Resources\Events;

use App\Enums\CustomFieldType;
use App\Models\EventAttendee;
use App\Models\EventCustomFieldResponse;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EventAttendee
 */
class EventAttendeeDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->public_name,
            ],
            'faction' => $this->faction ? [
                'id' => $this->faction->id,
                'name' => $this->faction->name,
            ] : null,
            'clubs' => $this->user->clubs->map(fn ($club) => [
                'id' => $club->id,
                'name' => $club->name,
            ])->values(),
            'army_list' => $this->army_list,
            'checked_in_at' => $this->checked_in_at?->toIso8601ZuluString(),
            'custom_field_responses' => $this->customFieldResponses
                ->sortBy(fn (EventCustomFieldResponse $response): int => $response->field->display_order)
                ->values()
                ->map(fn (EventCustomFieldResponse $response): array => [
                    'id' => $response->field->id,
                    'name' => $response->field->name,
                    'type' => $response->field->type->value,
                    'value' => $this->interpretValue($response),
                ])
                ->all(),
            'games' => $this->games
                ->sortBy(fn (Game $game): int => $game->round->number)
                ->values()
                ->map(fn (Game $game): array => [
                    'id' => $game->id,
                    'round_number' => $game->round->number,
                    'table_number' => $game->table_number,
                    'is_bye' => $game->is_bye,
                    'score' => $game->pivot?->score,
                    'opponents' => $game->attendees
                        ->reject(fn (EventAttendee $a): bool => $a->id === $this->id)
                        ->values()
                        ->map(fn (EventAttendee $a): array => [
                            'id' => $a->id,
                            'name' => $a->user->public_name,
                        ])->all(),
                ])->all(),
        ];
    }

    private function interpretValue(EventCustomFieldResponse $response): string|int|bool|null
    {
        if ($response->value === null) {
            return null;
        }

        return match ($response->field->type) {
            CustomFieldType::Checkbox => filter_var($response->value, FILTER_VALIDATE_BOOLEAN),
            CustomFieldType::Number => (int) $response->value,
            default => $response->value,
        };
    }
}
