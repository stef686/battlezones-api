<?php

namespace App\Http\Requests\Events\Concerns;

use App\Enums\SortDirection;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

/**
 * The rules an ordered set of Score Types is held to, wherever it is written.
 *
 * Replacing an Event's set and creating an Event with one are the same set
 * under the same rules (ADR 0005); only what an `id` may point at differs, so
 * each request supplies that rule itself.
 */
trait ValidatesScoreTypeSet
{
    /**
     * @return array<string, array<mixed>>
     */
    protected function scoreTypeSetRules(): array
    {
        return [
            // An Event scored on nothing has no Standings and nothing for a
            // Player to enter, so an empty set is a mistake, not a choice.
            'score_types' => ['required', 'array', 'min:1'],
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
            // validateDerivedPoints().
            'score_types.*.win_points' => ['nullable', 'numeric'],
            'score_types.*.draw_points' => ['nullable', 'numeric'],
            'score_types.*.loss_points' => ['nullable', 'numeric'],
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
     * A Game listing has room for one number per team, so at most one column
     * may claim it. The server still resolves a winner when none is marked.
     */
    protected function validateOneLeader(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $leaders = array_keys(array_filter(
            $this->scoreTypes(),
            fn (array $row): bool => $this->isTrue($row['is_primary'] ?? false),
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
    protected function validateDerivedPoints(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        foreach ($this->scoreTypes() as $index => $row) {
            if (! $this->isTrue($row['is_derived'] ?? false)) {
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
     * A flag as the `boolean` rule reads it, so `1` and `"1"` count as true
     * here exactly as they passed validation as true.
     */
    private function isTrue(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
