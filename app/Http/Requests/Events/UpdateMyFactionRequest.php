<?php

namespace App\Http\Requests\Events;

use App\Models\Event;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('faction_id', 'integer', 'The Faction this Player is bringing, or null to withdraw the choice.', required: true, example: 3)]
class UpdateMyFactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'faction_id' => [
                'present',
                'nullable',
                // Scoped to the Event's game system: a Faction from another
                // game is not a typo to correct later, it is unpairable data.
                Rule::exists('factions', 'id')->where('game_system_id', $this->event()->game_system_id),
            ],
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
