<?php

namespace App\Http\Requests\Events;

use App\Enums\Country;
use App\Http\Requests\Events\Concerns\ValidatesScoreTypeSet;
use App\Models\Event;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Knuckles\Scribe\Attributes\BodyParam;

/**
 * An Event as an Organiser's wizard submits it, whole.
 *
 * One request rather than a Draft patched step by step, so an abandoned wizard
 * leaves nothing behind and an Event exists either with a Lead and a scoring
 * set or not at all. The slug is chosen here and never again; status is not
 * accepted, because every Event starts as a Draft. See
 * docs/adr/0006-events-are-created-in-one-request.md before adding a field,
 * and ask whether PATCH should take it too.
 */
#[BodyParam('name', 'string', 'What the Event is called.', required: true, example: 'Croydon Doubles')]
#[BodyParam('slug', 'string', 'The Event\'s address: lowercase letters, digits and hyphens, 3 to 60 characters. It can never be changed.', required: true, example: 'croydon-doubles')]
#[BodyParam('starts_at', 'string', 'When the Event starts, as an ISO 8601 timestamp. Not in the past.', required: true, example: '2027-03-06T09:00:00+00:00')]
#[BodyParam('ends_at', 'string', 'When the Event ends, as an ISO 8601 timestamp. Not before it starts.', required: true, example: '2027-03-07T18:00:00+00:00')]
#[BodyParam('timezone', 'string', 'The IANA timezone the venue keeps.', required: true, example: 'Europe/London')]
#[BodyParam('venue_name', 'string', 'The venue.', required: false, example: 'The Hall')]
#[BodyParam('venue_address', 'string', 'Street address of the venue.', required: false, example: '1 Example Street')]
#[BodyParam('venue_city', 'string', 'Town or city of the venue.', required: false, example: 'Croydon')]
#[BodyParam('venue_country', 'string', 'Two-letter country code of the venue.', required: false, example: 'GB')]
#[BodyParam('game_system_id', 'integer', 'The Game System the Event is played under.', required: true, example: 4)]
#[BodyParam('attendee_size', 'integer', 'How many Players make up an Attendee: 1 for singles, up to 8.', required: true, example: 2)]
#[BodyParam('max_attendees', 'integer', 'How many parties may enter. Null for no limit.', required: false, example: 24)]
#[BodyParam('score_types', 'object[]', 'The complete ordered set the Event is scored on, as the Score Types endpoint takes it but with no ids. At least one.', required: true)]
#[BodyParam('score_types[].name', 'string', 'What the column is called.', required: true, example: 'Match Points')]
#[BodyParam('score_types[].abbreviation', 'string', 'The heading shown over the column. Left out or blank, the platform works one out from the name.', required: false, example: 'MP')]
#[BodyParam('score_types[].sort_direction', 'string', 'Which way up it ranks: asc where lower is better, desc where higher is.', required: true, example: 'desc')]
#[BodyParam('score_types[].is_derived', 'boolean', 'Whether the platform works it out from the result rather than a Player entering it.', required: true, example: true)]
#[BodyParam('score_types[].is_primary', 'boolean', 'Whether it leads a Game listing. At most one may.', required: true, example: true)]
#[BodyParam('score_types[].counts_for_ranking', 'boolean', 'Whether it ranks the Standings.', required: true, example: true)]
#[BodyParam('score_types[].win_points', 'number', 'What a win is worth. Required on a derived column.', required: false, example: 3)]
#[BodyParam('score_types[].draw_points', 'number', 'What a draw is worth. Required on a derived column.', required: false, example: 1)]
#[BodyParam('score_types[].loss_points', 'number', 'What a loss is worth. Required on a derived column.', required: false, example: 0)]
class StoreEventRequest extends FormRequest
{
    use ValidatesScoreTypeSet;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Event::class) === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'between:3,60', 'regex:/^[a-z0-9-]+$/', Rule::unique('events', 'slug')],
            'starts_at' => ['required', 'date', 'after_or_equal:now'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'timezone' => ['required', 'string', 'timezone:all'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'venue_address' => ['nullable', 'string', 'max:255'],
            'venue_city' => ['nullable', 'string', 'max:255'],
            'venue_country' => ['nullable', Rule::enum(Country::class)],
            'game_system_id' => ['required', 'integer', Rule::exists('game_systems', 'id')],
            'attendee_size' => ['required', 'integer', 'between:1,8'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],

            ...$this->scoreTypeSetRules(),
            // Every column is new: there is no Event yet for an id to belong to.
            'score_types.*.id' => ['prohibited'],

            // An Event is always created as a Draft; going public is its own
            // transition. Refused rather than dropped, so a caller is told.
            'status' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'The URL may only use lowercase letters, numbers and hyphens.',
            'slug.unique' => 'That URL is already taken.',
            'starts_at.after_or_equal' => 'The event cannot start in the past.',
            'ends_at.after_or_equal' => 'The event cannot end before it starts.',
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->validateOneLeader($validator),
            fn (Validator $validator) => $this->validateDerivedPoints($validator),
        ];
    }

    /**
     * The Event's own fields, without the scoring set written beside it.
     *
     * @return array<string, mixed>
     */
    public function eventAttributes(): array
    {
        return $this->safe()->except('score_types');
    }
}
