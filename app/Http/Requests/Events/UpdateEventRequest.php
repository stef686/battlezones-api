<?php

namespace App\Http\Requests\Events;

use App\Enums\Country;
use App\Models\Event;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Knuckles\Scribe\Attributes\BodyParam;

/**
 * The Event fields an Organiser may change, and only those.
 *
 * The refuse list is the decision here, not the accept list: slug is in every
 * Invite email already sent, attendee_size is the shape every existing
 * registration was built at, and status and pairing_format drive Round
 * generation rather than presentation. See
 * docs/adr/0002-general-event-patch-endpoint.md before adding a field.
 */
#[BodyParam('name', 'string', 'What the Event is called.', required: false, example: 'London Grand Tournament')]
#[BodyParam('description', 'string', 'The blurb Players read on the Event screen.', required: false, example: 'A two-day Horus Heresy doubles event.')]
#[BodyParam('venue_name', 'string', 'The venue.', required: false, example: 'The Hall')]
#[BodyParam('venue_address', 'string', 'Street address of the venue.', required: false, example: '1 Example Street')]
#[BodyParam('venue_city', 'string', 'Town or city of the venue.', required: false, example: 'London')]
#[BodyParam('venue_country', 'string', 'Two-letter country code of the venue.', required: false, example: 'GB')]
#[BodyParam('starts_at', 'string', 'When the Event starts, as an ISO 8601 timestamp.', required: false, example: '2026-09-12T09:00:00+01:00')]
#[BodyParam('ends_at', 'string', 'When the Event ends, as an ISO 8601 timestamp.', required: false, example: '2026-09-13T18:00:00+01:00')]
#[BodyParam('registration_closes_at', 'string', 'When entry closes, as an ISO 8601 timestamp.', required: false, example: '2026-09-05T23:59:00+01:00')]
#[BodyParam('max_attendees', 'integer', 'How many parties may enter. Null for no limit, and never fewer than have already entered.', required: false, example: 32)]
class UpdateEventRequest extends FormRequest
{
    /**
     * An Event that is not publicly visible answers 404 to anyone but its
     * Organisers, exactly as reading it does: a 403 would confirm it exists.
     */
    protected function prepareForValidation(): void
    {
        $event = $this->event();

        abort_unless(
            $event->status->isPubliclyVisible() || $event->isOrganisedBy($this->user()),
            404,
        );
    }

    public function authorize(): bool
    {
        return $this->user()?->can('organise', $this->event()) === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'venue_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'venue_address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'venue_city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'venue_country' => ['sometimes', 'nullable', Rule::enum(Country::class)],
            'starts_at' => ['sometimes', 'nullable', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:'.$this->resolvedStart()],
            'registration_closes_at' => ['sometimes', 'nullable', 'date'],
            // An Event already fuller than the new cap is a validation failure
            // rather than an accepted over-fill: nobody is being turned out.
            'max_attendees' => ['sometimes', 'nullable', 'integer', 'min:'.max(1, $this->event()->attendees()->count())],

            // Refused loudly rather than quietly dropped, so a caller that
            // tries is told why instead of watching a change not happen.
            'slug' => ['prohibited'],
            'attendee_size' => ['prohibited'],
            'status' => ['prohibited'],
            'pairing_format' => ['prohibited'],
        ];
    }

    /**
     * The start this Event will have once the change lands, so a lone
     * `ends_at` is still checked against the day the Event actually begins.
     */
    private function resolvedStart(): string
    {
        $start = $this->has('starts_at')
            ? $this->input('starts_at')
            : $this->event()->starts_at;

        if ($start === null) {
            return Carbon::createFromTimestamp(0)->toIso8601String();
        }

        return Carbon::parse(is_string($start) ? $start : (string) $start)->toIso8601String();
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
