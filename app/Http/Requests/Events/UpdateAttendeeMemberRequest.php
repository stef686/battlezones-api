<?php

namespace App\Http\Requests\Events;

use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventAttendeeMembership;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('name', 'string', 'The Player\'s name.', required: false, example: 'Tarik Torgaddon')]
#[BodyParam('email', 'string', 'The address their invitation is sent to.', required: false, example: 'tarik@example.com')]
#[BodyParam('faction_id', 'integer', 'The Faction this Player brings.', required: false, example: 1)]
class UpdateAttendeeMemberRequest extends FormRequest
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
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email'],
            'faction_id' => [
                'sometimes',
                'nullable',
                Rule::exists('factions', 'id')->where('game_system_id', $this->event()->game_system_id),
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty() || ! $this->has('email')) {
                    return;
                }

                // A corrected address that belongs to somebody already in this
                // Event would move the seat onto a Player who is entered twice,
                // which the schema refuses and the standings could not survive.
                if ($this->alreadyEntered()) {
                    $validator->errors()->add('email', 'This player has already entered this event.');
                }
            },
        ];
    }

    private function alreadyEntered(): bool
    {
        $user = User::where('email', $this->string('email')->toString())->first();

        if (! $user instanceof User || $user->getKey() === $this->membership()->user_id) {
            return false;
        }

        return EventAttendeeMembership::query()
            ->where('event_id', $this->event()->getKey())
            ->where('user_id', $user->getKey())
            ->exists();
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
    public function attendee(): EventAttendee
    {
        $attendee = $this->route('attendee');

        return $attendee instanceof EventAttendee ? $attendee : new EventAttendee();
    }

    /**
     * Empty when route model binding has not run, which only happens where the
     * docs generator instantiates this request outside a real request cycle.
     */
    public function membership(): EventAttendeeMembership
    {
        $membership = $this->route('membership');

        return $membership instanceof EventAttendeeMembership ? $membership : new EventAttendeeMembership();
    }
}
