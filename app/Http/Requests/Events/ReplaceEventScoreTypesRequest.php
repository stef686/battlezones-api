<?php

namespace App\Http\Requests\Events;

use App\Http\Requests\Events\Concerns\ValidatesScoreTypeSet;
use App\Models\Event;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Knuckles\Scribe\Attributes\BodyParam;

/**
 * The whole ordered set of Score Types an Event is scored on.
 *
 * Order is doing two jobs — display order, and ranking order among the rows
 * that count for it — so it is sent as one array rather than a call per row.
 * Slugs are never accepted: a Score Type is addressed by slug when a result is
 * submitted and when the Standings are sorted, so a slug that moved under a
 * client would refuse a result already in flight.
 */
#[BodyParam('score_types', 'object[]', 'The complete ordered set. Position sets display order, and position among the rows counting for ranking sets ranking order.', required: true)]
#[BodyParam('score_types[].id', 'integer', 'The Score Type being edited. Leave it out to add a new one.', required: false, example: 7)]
#[BodyParam('score_types[].name', 'string', 'What the column is called.', required: true, example: 'Battle Points')]
#[BodyParam('score_types[].abbreviation', 'string', 'The heading shown over the column on a Game and in the Standings. Left out or blank, the platform works one out from the name.', required: false, example: 'BP')]
#[BodyParam('score_types[].sort_direction', 'string', 'Which way up it ranks: asc where lower is better, desc where higher is.', required: true, example: 'desc')]
#[BodyParam('score_types[].is_derived', 'boolean', 'Whether the platform works it out from the result rather than a Player entering it.', required: true, example: false)]
#[BodyParam('score_types[].is_primary', 'boolean', 'Whether it leads a Game listing. At most one may.', required: true, example: true)]
#[BodyParam('score_types[].counts_for_ranking', 'boolean', 'Whether it ranks the Standings.', required: true, example: true)]
#[BodyParam('score_types[].win_points', 'number', 'What a win is worth. Required on a derived column.', required: false, example: 3)]
#[BodyParam('score_types[].draw_points', 'number', 'What a draw is worth. Required on a derived column.', required: false, example: 1)]
#[BodyParam('score_types[].loss_points', 'number', 'What a loss is worth. Required on a derived column.', required: false, example: 0)]
class ReplaceEventScoreTypesRequest extends FormRequest
{
    use ValidatesScoreTypeSet;

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
            ...$this->scoreTypeSetRules(),
            // Absent on a row being added: a Score Type the Event does not
            // have yet has no id to send.
            'score_types.*.id' => [
                'nullable',
                'integer',
                'distinct',
                Rule::exists('event_score_types', 'id')->where('event_id', $this->event()->getKey()),
            ],
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
            fn (Validator $validator) => $this->validateNothingScoredIsDropped($validator),
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
     * A column Games have been scored on cannot be dropped.
     *
     * `game_scores` cascades on delete, so this guard is the only thing
     * between an Organiser tidying a column away and the Event's whole score
     * history — and the Standings computed from it — going with it. An
     * unscored column is theirs to remove.
     */
    private function validateNothingScoredIsDropped(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $kept = array_filter(array_map(
            fn (array $row): ?int => isset($row['id']) ? (int) $row['id'] : null,
            $this->scoreTypes(),
        ));

        $scored = $this->event()->scoreTypes()
            ->whereNotIn('id', $kept)
            ->whereHas('scores')
            ->pluck('name');

        if ($scored->isNotEmpty()) {
            $validator->errors()->add(
                'score_types',
                'Games have already been scored on '.$scored->implode(', ').', so it cannot be removed.',
            );
        }
    }
}
