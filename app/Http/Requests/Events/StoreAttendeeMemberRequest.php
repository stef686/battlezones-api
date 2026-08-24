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

#[BodyParam('name', 'string', 'The Player\'s name, used if they have no account yet.', required: false, example: 'Tarik Torgaddon')]
#[BodyParam('email', 'string', 'The Player\'s email address.', required: true, example: 'tarik@example.com')]
#[BodyParam('faction_id', 'integer', 'The Faction this Player brings.', required: false, example: 1)]
#[BodyParam('army_list', 'string', 'This Player\'s army list.', required: false)]
class StoreAttendeeMemberRequest extends FormRequest
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
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'faction_id' => [
                'nullable',
                Rule::exists('factions', 'id')->where('game_system_id', $this->event()->game_system_id),
            ],
            'army_list' => ['nullable', 'string'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $attendee = $this->attendee();

                // Party size is checked here rather than in the schema so an
                // Organiser can repair a broken team without fighting it.
                if ($attendee->members()->count() >= $this->event()->attendee_size) {
                    $validator->errors()->add('email', 'This team is already full.');

                    return;
                }

                if ($this->alreadyEntered()) {
                    $validator->errors()->add('email', 'This player has already entered this event.');
                }
            },
        ];
    }

    private function alreadyEntered(): bool
    {
        $user = User::where('email', $this->string('email')->toString())->first();

        if (! $user instanceof User) {
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
}
