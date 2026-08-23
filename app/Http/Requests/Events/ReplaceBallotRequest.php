<?php

namespace App\Http\Requests\Events;

use App\Models\Event;
use App\Models\EventPoll;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('attendee_ids', 'integer[]', 'The complete Ballot: every Attendee this Player is picking. An empty array clears it.', required: true, example: [4, 9])]
class ReplaceBallotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $poll = $this->poll();

        abort_unless($poll->event_id === $this->event()->getKey(), 404);

        abort_unless($poll->isOpen(), 422, 'Voting is not open for this poll.');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'attendee_ids' => ['present', 'array', 'max:'.$this->poll()->votes_per_player],
            'attendee_ids.*' => ['integer', 'distinct'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->validateEligibility($validator),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'attendee_ids.max' => 'This poll allows :max pick(s).',
        ];
    }

    /**
     * @return list<int>
     */
    public function attendeeIds(): array
    {
        return array_values(array_map(intval(...), $this->array('attendee_ids')));
    }

    public function event(): Event
    {
        /** @var Event $event */
        $event = $this->route('event');

        return $event;
    }

    public function poll(): EventPoll
    {
        /** @var EventPoll $poll */
        $poll = $this->route('poll');

        return $poll;
    }

    private function validateEligibility(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $eligible = $this->poll()->eligibleSubjects($this->user())->pluck('id')->all();

        foreach ($this->attendeeIds() as $index => $attendeeId) {
            if (! in_array($attendeeId, $eligible, true)) {
                $validator->errors()->add("attendee_ids.{$index}", 'This attendee cannot be picked in this poll.');
            }
        }
    }
}
