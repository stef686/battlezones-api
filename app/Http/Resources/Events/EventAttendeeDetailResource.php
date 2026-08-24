<?php

namespace App\Http\Resources\Events;

use App\Enums\CustomFieldType;
use App\Http\Resources\Events\Concerns\SerialisesAttendeeMembers;
use App\Models\EventAttendee;
use App\Models\EventCustomFieldResponse;
use App\Models\Game;
use App\Models\GameScore;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EventAttendee
 */
class EventAttendeeDetailResource extends JsonResource
{
    use SerialisesAttendeeMembers;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->displayName(),
            'allegiance' => $this->allegiance?->value,
            'members' => $this->serialiseMembers($this->resource, withArmyList: true, withClubs: true),
            'checked_in_at' => $this->checked_in_at?->toIso8601ZuluString(),
            // Whether this army is on the display table, and the number it
            // sits under. Neither is a vote, and neither is secret.
            'painting_entered' => (bool) $this->painting_entered,
            'display_number' => $this->display_number,
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
                    'scores' => $game->scores
                        ->where('event_attendee_id', $this->id)
                        ->mapWithKeys(fn (GameScore $score) => [$score->scoreType->slug => $score->value])
                        ->toArray(),
                    'opponents' => $game->attendees
                        ->reject(fn (EventAttendee $a): bool => $a->id === $this->id)
                        ->values()
                        ->map(fn (EventAttendee $a): array => [
                            'id' => $a->id,
                            'name' => $a->displayName(),
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
