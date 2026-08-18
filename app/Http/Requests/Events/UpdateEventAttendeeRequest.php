<?php

namespace App\Http\Requests\Events;

use App\Enums\Allegiance;
use App\Models\EventAttendee;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('name', 'string', 'The name the party competes under.', required: false, example: 'Sons of Terra')]
#[BodyParam('allegiance', 'string', 'The side the party fights for. Frozen once a Round is Live.', required: false, example: 'traitor')]
class UpdateEventAttendeeRequest extends FormRequest
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
            'allegiance' => ['sometimes', 'nullable', Rule::enum(Allegiance::class)],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty() || ! $this->has('allegiance')) {
                    return;
                }

                $attendee = $this->attendee();

                if ($this->input('allegiance') === $attendee->allegiance?->value) {
                    return;
                }

                // Allegiance is a pairing constraint. Changing it after Games
                // have been published would retroactively invalidate them, so
                // it is refused to Organisers too and repaired in Filament.
                if ($attendee->event->hasLiveRound()) {
                    $validator->errors()->add(
                        'allegiance',
                        'Allegiance cannot change once a round is live.',
                    );
                }
            },
        ];
    }

    public function attendee(): EventAttendee
    {
        /** @var EventAttendee $attendee */
        $attendee = $this->route('attendee');

        return $attendee;
    }
}
