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
#[BodyParam('score_types[].id', 'integer', 'The Score Type being edited.', required: true, example: 7)]
#[BodyParam('score_types[].name', 'string', 'What the column is called.', required: true, example: 'Battle Points')]
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
            'score_types.*.id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('event_score_types', 'id')->where('event_id', $this->event()->getKey()),
            ],
            'score_types.*.name' => ['required', 'string', 'max:255'],
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
            fn (Validator $validator) => $this->validateNothingDropped($validator),
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
     * Every column the Event has must be in the payload. Adding and removing
     * columns is its own endpoint's job; a row quietly left out here would
     * otherwise read as a request to destroy it and its scores.
     */
    private function validateNothingDropped(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $sent = array_map(intval(...), array_column($this->scoreTypes(), 'id'));
        $missing = $this->event()->scoreTypes()->whereNotIn('id', $sent)->pluck('name');

        if ($missing->isNotEmpty()) {
            $validator->errors()->add(
                'score_types',
                'Every column must be sent: '.$missing->implode(', ').' is missing.',
            );
        }
    }
}
