<?php

namespace App\Http\Requests\Events;

use App\Models\Event;
use App\Models\EventAttendee;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('painting_entered', 'boolean', 'Whether this Attendee has an army on the display table.', required: false, example: true)]
#[BodyParam('display_number', 'integer', 'The number their army sits under. Independent of entry: teams get ticked off before anyone numbers them.', required: false, example: 14)]
class UpdatePaintingEntryRequest extends FormRequest
{
    /**
     * Organisers do both; a Player only enters their own army.
     *
     * Entering is the Player saying their army is on the table, which is
     * theirs to say. The display number is whoever laid the table out saying
     * where it sits, and two teams picking the same number is a mess in the
     * room rather than a mistake in a record.
     */
    public function authorize(): bool
    {
        if ($this->event()->isOrganisedBy($this->user())) {
            return true;
        }

        return ! $this->has('display_number') && $this->attendee()->hasMember($this->user());
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'painting_entered' => ['sometimes', 'boolean'],
            'display_number' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:9999'],
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
    public function attendee(): EventAttendee
    {
        $attendee = $this->route('attendee');

        return $attendee instanceof EventAttendee ? $attendee : new EventAttendee();
    }
}
