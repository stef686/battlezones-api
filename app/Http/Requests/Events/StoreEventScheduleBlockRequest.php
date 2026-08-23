<?php

namespace App\Http\Requests\Events;

use App\Enums\ScheduleBlockType;
use App\Models\Event;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('label', 'string', 'What the block is called on the schedule.', required: true, example: 'Round 1')]
#[BodyParam('type', 'string', 'One of info, round, painting_voting.', required: true, example: 'round')]
#[BodyParam('starts_at', 'string', 'When the block starts, as an ISO 8601 timestamp.', required: true, example: '2026-07-11T09:00:00+01:00')]
#[BodyParam('ends_at', 'string', 'When the block ends, as an ISO 8601 timestamp.', required: true, example: '2026-07-11T11:30:00+01:00')]
#[BodyParam('round_id', 'integer', 'The Round this block runs. Required for a round block, and rejected on any other type.', required: false, example: 4)]
class StoreEventScheduleBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->event()->isOrganisedBy($this->user());
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(ScheduleBlockType::class)],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'display_order' => ['sometimes', 'integer', 'min:0'],

            // Only a round block points at a row: an info block always renders
            // as plain text, and painting voting is one Event-level window.
            'round_id' => [
                Rule::requiredIf(fn (): bool => $this->input('type') === ScheduleBlockType::Round->value),
                Rule::prohibitedIf(fn (): bool => $this->input('type') !== ScheduleBlockType::Round->value),
                'integer',
                Rule::exists('rounds', 'id')->where('event_id', $this->event()->getKey()),
            ],
        ];
    }

    public function event(): Event
    {
        /** @var Event $event */
        $event = $this->route('event');

        return $event;
    }
}
