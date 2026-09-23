<?php

namespace App\Http\Requests\Events;

use App\Enums\SortDirection;
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
            // An Event scored on nothing has no Standings and nothing for a
            // Player to enter, so an empty set is a mistake, not a choice.
            'score_types' => ['required', 'array', 'min:1'],
            // Absent on a row being added: a Score Type the Event does not
            // have yet has no id to send.
            'score_types.*.id' => [
                'nullable',
                'integer',
                'distinct',
                Rule::exists('event_score_types', 'id')->where('event_id', $this->event()->getKey()),
            ],
            'score_types.*.name' => ['required', 'string', 'max:255'],
            // Optional: an Organiser who does not want to think about it gets
            // the initials of the name, which is what the clients showed
            // before the column existed. Short enough to sit over a number.
            'score_types.*.abbreviation' => ['nullable', 'string', 'max:8'],
            'score_types.*.sort_direction' => ['required', Rule::enum(SortDirection::class)],
            'score_types.*.is_derived' => ['required', 'boolean'],
            'score_types.*.is_primary' => ['required', 'boolean'],
            'score_types.*.counts_for_ranking' => ['required', 'boolean'],
            // Only meaningful on a derived column, and required there: see
            // the after() rules.
            'score_types.*.win_points' => ['nullable', 'numeric'],
            'score_types.*.draw_points' => ['nullable', 'numeric'],
            'score_types.*.loss_points' => ['nullable', 'numeric'],
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
     * The set as it will be written, in the order it arrived.
     *
     * @return list<array<string, mixed>>
     */
    public function scoreTypes(): array
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = array_values($this->array('score_types'));

        return $rows;
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
     * A Game listing has room for one number per team, so at most one column
     * may claim it. The server still resolves a winner when none is marked.
     */
    private function validateOneLeader(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $leaders = array_keys(array_filter(
            $this->scoreTypes(),
            fn (array $row): bool => (bool) ($row['is_primary'] ?? false),
        ));

        foreach (array_slice($leaders, 1) as $index) {
            $validator->errors()->add(
                "score_types.{$index}.is_primary",
                'Only one column can lead a game listing.',
            );
        }
    }

    /**
     * A derived column is worked out from the result, which cannot be done
     * without knowing what a win, a draw and a loss are worth.
     */
    private function validateDerivedPoints(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        foreach ($this->scoreTypes() as $index => $row) {
            if (($row['is_derived'] ?? false) !== true) {
                continue;
            }

            foreach (['win_points', 'draw_points', 'loss_points'] as $field) {
                if (($row[$field] ?? null) === null) {
                    $validator->errors()->add(
                        "score_types.{$index}.{$field}",
                        'A worked-out column needs its win, draw and loss points.',
                    );
                }
            }
        }
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
