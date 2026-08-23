<?php

namespace App\Http\Requests\Events;

use App\Enums\ScheduleBlockType;
use App\Models\Event;
use App\Models\EventScheduleBlock;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('label', 'string', 'What the block is called on the schedule.', required: false, example: 'Round 1')]
#[BodyParam('type', 'string', 'One of info, round, painting_voting.', required: false, example: 'round')]
#[BodyParam('starts_at', 'string', 'When the block starts, as an ISO 8601 timestamp.', required: false, example: '2026-07-11T09:00:00+01:00')]
#[BodyParam('ends_at', 'string', 'When the block ends, as an ISO 8601 timestamp.', required: false, example: '2026-07-11T11:30:00+01:00')]
#[BodyParam('round_id', 'integer', 'The Round this block runs. Required for a round block, and rejected on any other type.', required: false, example: 4)]
class UpdateEventScheduleBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->event()->isOrganisedBy($this->user());
    }

    protected function prepareForValidation(): void
    {
        abort_unless($this->block()->event_id === $this->event()->getKey(), 404);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'label' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', Rule::enum(ScheduleBlockType::class)],
            'starts_at' => ['sometimes', 'date'],
            'ends_at' => ['sometimes', 'date', 'after:'.$this->resolvedStart()->toIso8601String()],
            'display_order' => ['sometimes', 'integer', 'min:0'],
            'round_id' => [
                Rule::requiredIf(fn (): bool => $this->resolvedType() === ScheduleBlockType::Round),
                Rule::prohibitedIf(fn (): bool => $this->resolvedType() !== ScheduleBlockType::Round),
                'integer',
                Rule::exists('rounds', 'id')->where('event_id', $this->event()->getKey()),
            ],
        ];
    }

    /**
     * The start time this block will have once the change lands, so a lone
     * `ends_at` is still checked against the time the block actually begins.
     */
    private function resolvedStart(): Carbon
    {
        return $this->has('starts_at')
            ? Carbon::parse((string) $this->input('starts_at'))
            : $this->block()->starts_at;
    }

    /**
     * The type this block will have once the change lands.
     */
    private function resolvedType(): ScheduleBlockType
    {
        return $this->has('type')
            ? ScheduleBlockType::from((string) $this->input('type'))
            : $this->block()->type;
    }

    public function event(): Event
    {
        /** @var Event $event */
        $event = $this->route('event');

        return $event;
    }

    public function block(): EventScheduleBlock
    {
        /** @var EventScheduleBlock $block */
        $block = $this->route('block');

        return $block;
    }
}
