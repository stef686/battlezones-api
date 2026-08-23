<?php

namespace App\Http\Requests\Events;

use App\Enums\PollType;
use App\Models\Event;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('name', 'string', 'What the Poll is called.', required: true, example: 'Best Painted Army')]
#[BodyParam('type', 'string', 'Which Attendees may be picked: painting or favourite_opponent.', required: true, example: 'painting')]
#[BodyParam('votes_per_player', 'integer', 'How many Attendees each Player may pick. Defaults to one.', required: false, example: 3)]
class StoreEventPollRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(PollType::class)],
            'votes_per_player' => ['sometimes', 'integer', 'min:1', 'max:20'],
        ];
    }

    /**
     * Empty when route model binding has not run, which only happens where the
     * docs generator instantiates this request outside a real request cycle.
     */
    public function event(): Event
    {
        $event = $this->route('event');

        return $event instanceof Event ? $event : new Event();
    }
}
