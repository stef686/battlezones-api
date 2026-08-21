<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Minimum-cost assignment on a square cost matrix — the Hungarian algorithm in
 * its O(n^3) shortest-augmenting-path form.
 *
 * Pairing a Round is an assignment problem, not a sorting one. A greedy pass
 * makes locally-fine early choices and strands the last few rows with whatever
 * columns are left, which is where the four-wins-against-one-win games people
 * notice come from. This solves it exactly instead.
 *
 * Ties are broken towards the lowest column index, so the same matrix always
 * yields the same assignment.
 */
class HungarianMatcher
{
    /**
     * @param  array<int, array<int, int>>  $cost  a square matrix, row-major
     * @return array<int, int> the column assigned to each row
     */
    public function solve(array $cost): array
    {
        $size = count($cost);

        if ($size === 0) {
            return [];
        }

        foreach ($cost as $row) {
            if (count($row) !== $size) {
                throw new InvalidArgumentException('The cost matrix must be square.');
            }
        }

        /** @var array<int, int> $rowPotential */
        $rowPotential = array_fill(0, $size + 1, 0);
        /** @var array<int, int> $columnPotential */
        $columnPotential = array_fill(0, $size + 1, 0);
        /** @var array<int, int> $rowOfColumn column (1-indexed, 0 = free) => row (1-indexed) */
        $rowOfColumn = array_fill(0, $size + 1, 0);
        /** @var array<int, int> $previousColumn */
        $previousColumn = array_fill(0, $size + 1, 0);

        for ($row = 1; $row <= $size; $row++) {
            $rowOfColumn[0] = $row;
            $column = 0;

            $slack = array_fill(0, $size + 1, PHP_INT_MAX);
            $visited = array_fill(0, $size + 1, false);

            do {
                $visited[$column] = true;
                $currentRow = $rowOfColumn[$column];
                $delta = PHP_INT_MAX;
                $nextColumn = 0;

                for ($candidate = 1; $candidate <= $size; $candidate++) {
                    if ($visited[$candidate]) {
                        continue;
                    }

                    $reduced = $cost[$currentRow - 1][$candidate - 1]
                        - $rowPotential[$currentRow]
                        - $columnPotential[$candidate];

                    if ($reduced < $slack[$candidate]) {
                        $slack[$candidate] = $reduced;
                        $previousColumn[$candidate] = $column;
                    }

                    if ($slack[$candidate] < $delta) {
                        $delta = $slack[$candidate];
                        $nextColumn = $candidate;
                    }
                }

                for ($candidate = 0; $candidate <= $size; $candidate++) {
                    if ($visited[$candidate]) {
                        $rowPotential[$rowOfColumn[$candidate]] += $delta;
                        $columnPotential[$candidate] -= $delta;
                    } else {
                        $slack[$candidate] -= $delta;
                    }
                }

                $column = $nextColumn;
            } while ($rowOfColumn[$column] !== 0);

            do {
                $source = $previousColumn[$column];
                $rowOfColumn[$column] = $rowOfColumn[$source];
                $column = $source;
            } while ($column !== 0);
        }

        $assignment = array_fill(0, $size, 0);

        for ($column = 1; $column <= $size; $column++) {
            $assignment[$rowOfColumn[$column] - 1] = $column - 1;
        }

        return $assignment;
    }
}
