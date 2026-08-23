<?php

namespace App\Http\Requests\Events;

use App\Models\Event;
use App\Models\Round;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('game_ids', 'integer[]', 'The two Games to recombine. The exchange itself is not a choice: the system performs the only legal one.', required: true, example: [12, 15])]
class SwapRoundPairingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->event()->isOrganisedBy($this->user());
    }

    protected function prepareForValidation(): void
    {
        abort_unless($this->round()->event_id === $this->event()->getKey(), 404);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'game_ids' => ['required', 'array', 'size:2'],
            'game_ids.*' => ['required', 'integer'],
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

    /**
     * Empty when route model binding has not run, which only happens where the
     * docs generator instantiates this request outside a real request cycle.
     */
    public function round(): Round
    {
        $round = $this->route('round');

        return $round instanceof Round ? $round : new Round();
    }
}
