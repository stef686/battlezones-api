<?php

use App\Services\HungarianMatcher;

/**
 * @param  array<int, array<int, int>>  $cost
 * @param  array<int, int>  $assignment
 */
function totalCost(array $cost, array $assignment): int
{
    return collect($assignment)->sum(fn (int $column, int $row): int => $cost[$row][$column]);
}

test('an empty matrix has an empty assignment', function () {
    expect((new HungarianMatcher())->solve([]))->toBe([]);
});

test('a one-by-one matrix assigns the only cell', function () {
    expect((new HungarianMatcher())->solve([[7]]))->toBe([0]);
});

test('it finds the optimum a greedy pass would miss', function () {
    $cost = [
        [1, 2, 3],
        [2, 4, 6],
        [3, 6, 9],
    ];

    $assignment = (new HungarianMatcher())->solve($cost);

    // Taking the cheapest cell for each row in turn costs 1 + 4 + 9 = 14.
    expect($assignment)->toBe([2, 1, 0])
        ->and(totalCost($cost, $assignment))->toBe(10);
});

test('it returns the same assignment every time', function () {
    $cost = [
        [4, 4, 0, 0],
        [4, 4, 0, 0],
        [0, 0, 4, 4],
        [0, 0, 4, 4],
    ];

    $matcher = new HungarianMatcher();

    expect($matcher->solve($cost))->toBe($matcher->solve($cost))
        ->and(totalCost($cost, $matcher->solve($cost)))->toBe(0);
});

test('it minimises the total across every permutation of a fixed matrix', function () {
    $cost = [
        [8, 2, 9, 5],
        [3, 7, 1, 6],
        [4, 4, 8, 2],
        [9, 6, 3, 7],
    ];

    $assignment = (new HungarianMatcher())->solve($cost);

    $best = collect(permutationsOfFour())->min(fn (array $p): int => totalCost($cost, $p));

    expect(totalCost($cost, $assignment))->toBe($best);
});

/**
 * @return array<int, array<int, int>>
 */
function permutationsOfFour(): array
{
    $permutations = [];

    foreach ([0, 1, 2, 3] as $a) {
        foreach (array_diff([0, 1, 2, 3], [$a]) as $b) {
            foreach (array_diff([0, 1, 2, 3], [$a, $b]) as $c) {
                $d = array_values(array_diff([0, 1, 2, 3], [$a, $b, $c]))[0];
                $permutations[] = [$a, $b, $c, $d];
            }
        }
    }

    return $permutations;
}
