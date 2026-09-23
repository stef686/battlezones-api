<?php

use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventScoreType;
use App\Models\Game;
use App\Models\GameScore;
use App\Models\Round;
use App\Models\User;

test('an organiser reads the columns their event is scored on, in the order they are shown', function () {
    $event = Event::factory()->published()->create();

    EventScoreType::factory()->victoryPoints()->primary()->for($event)->create(['display_order' => 1]);
    EventScoreType::factory()->matchPoints()->rankedAt(1)->for($event)->create(['display_order' => 0]);

    $this->actingAs(organiserOf($event))
        ->getJson(route('events.score-types.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonPath('data.0.name', 'Match Points')
        ->assertJsonPath('data.0.slug', 'match-points')
        ->assertJsonPath('data.0.is_derived', true)
        ->assertJsonPath('data.0.counts_for_ranking', true)
        ->assertJsonPath('data.0.win_points', '3.00')
        ->assertJsonPath('data.1.name', 'Victory Points')
        ->assertJsonPath('data.1.is_primary', true)
        ->assertJsonPath('data.1.counts_for_ranking', false)
        ->assertJsonPath('data.1.sort_direction', 'desc');
});

test('an organiser sees which columns already carry scores', function () {
    [$event] = submittedGame();

    EventScoreType::factory()->for($event)->create(['name' => 'Painting', 'slug' => 'painting', 'display_order' => 2]);

    $this->actingAs(organiserOf($event))
        ->getJson(route('events.score-types.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonPath('data.0.slug', 'victory-points')
        ->assertJsonPath('data.0.is_scored', true)
        ->assertJsonPath('data.1.slug', 'painting')
        ->assertJsonPath('data.1.is_scored', false);
});

test('somebody who does not run the event is refused its scoring', function () {
    $event = Event::factory()->published()->create();
    EventScoreType::factory()->victoryPoints()->for($event)->create();

    $this->actingAs(User::factory()->create())
        ->getJson(route('events.score-types.index', ['event' => $event->slug]))
        ->assertForbidden();
});

test('renaming a column keeps its slug, and every score already recorded under it', function () {
    [$event] = submittedGame();
    $victoryPoints = $event->scoreTypes()->firstOrFail();

    $this->actingAs(organiserOf($event))
        ->putJson(route('events.score-types.replace', ['event' => $event->slug]), [
            'score_types' => [[
                'id' => $victoryPoints->id,
                'name' => 'Battle Points',
                'sort_direction' => 'desc',
                'is_derived' => false,
                'is_primary' => true,
                'counts_for_ranking' => true,
            ]],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.0.name', 'Battle Points')
        ->assertJsonPath('data.0.slug', 'victory-points')
        ->assertJsonPath('data.0.is_scored', true);

    expect($victoryPoints->refresh()->name)->toBe('Battle Points')
        ->and($victoryPoints->slug)->toBe('victory-points')
        ->and($victoryPoints->scores()->count())->toBe(2);
});

test('the order columns are sent in is both the order they are shown and the order they rank', function () {
    $event = pairableEvent();
    $matchPoints = $event->scoreTypes()->where('slug', 'match-points')->firstOrFail();
    $victoryPoints = $event->scoreTypes()->where('slug', 'victory-points')->firstOrFail();

    $this->actingAs(organiserOf($event))
        ->putJson(route('events.score-types.replace', ['event' => $event->slug]), [
            'score_types' => [
                [
                    'id' => $victoryPoints->id,
                    'name' => 'Victory Points',
                    'sort_direction' => 'desc',
                    'is_derived' => false,
                    'is_primary' => true,
                    'counts_for_ranking' => true,
                ],
                [
                    'id' => $matchPoints->id,
                    'name' => 'Match Points',
                    'sort_direction' => 'desc',
                    'is_derived' => true,
                    'is_primary' => false,
                    'counts_for_ranking' => false,
                    'win_points' => 3,
                    'draw_points' => 1,
                    'loss_points' => 0,
                ],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.0.slug', 'victory-points')
        ->assertJsonPath('data.1.slug', 'match-points');

    expect($victoryPoints->refresh()->display_order)->toBe(0)
        ->and($victoryPoints->ranking_order)->toBe(1)
        ->and($matchPoints->refresh()->display_order)->toBe(1)
        ->and($matchPoints->ranking_order)->toBeNull();
});

test('scoring an event cannot be set up in a way nothing can be worked out from', function () {
    $event = pairableEvent();
    $matchPoints = $event->scoreTypes()->where('slug', 'match-points')->firstOrFail();
    $victoryPoints = $event->scoreTypes()->where('slug', 'victory-points')->firstOrFail();
    $organiser = organiserOf($event);

    $rows = fn (array $overrides = []) => [
        'score_types' => [
            array_merge([
                'id' => $matchPoints->id,
                'name' => 'Match Points',
                'sort_direction' => 'desc',
                'is_derived' => true,
                'is_primary' => true,
                'counts_for_ranking' => true,
                'win_points' => 3,
                'draw_points' => 1,
                'loss_points' => 0,
            ], $overrides),
            [
                'id' => $victoryPoints->id,
                'name' => 'Victory Points',
                'sort_direction' => 'desc',
                'is_derived' => false,
                'is_primary' => true,
                'counts_for_ranking' => true,
            ],
        ],
    ];

    // Two columns claiming the lead: a Game listing has room for one.
    $this->actingAs($organiser)
        ->putJson(route('events.score-types.replace', ['event' => $event->slug]), $rows())
        ->assertJsonValidationErrors(['score_types.1.is_primary' => 'Only one column can lead a game listing.']);

    // A worked-out column with nothing to work it out with.
    $this->actingAs($organiser)
        ->putJson(route('events.score-types.replace', ['event' => $event->slug]), $rows([
            'is_primary' => false,
            'win_points' => null,
            'draw_points' => null,
            'loss_points' => null,
        ]))
        ->assertJsonValidationErrors([
            'score_types.0.win_points' => 'A worked-out column needs its win, draw and loss points.',
            'score_types.0.draw_points',
            'score_types.0.loss_points',
        ]);

    // An Event scored on nothing at all.
    $this->actingAs($organiser)
        ->putJson(route('events.score-types.replace', ['event' => $event->slug]), ['score_types' => []])
        ->assertJsonValidationErrors(['score_types']);

    expect($matchPoints->refresh()->name)->toBe('Match Points')
        ->and($event->scoreTypes()->count())->toBe(2);
});

test('somebody who does not run the event cannot change what it is scored on', function () {
    $event = pairableEvent();
    $scoreType = $event->scoreTypes()->firstOrFail();

    $this->actingAs(User::factory()->create())
        ->putJson(route('events.score-types.replace', ['event' => $event->slug]), [
            'score_types' => [[
                'id' => $scoreType->id,
                'name' => 'Whatever I Like',
                'sort_direction' => 'desc',
                'is_derived' => false,
                'is_primary' => true,
                'counts_for_ranking' => true,
            ]],
        ])
        ->assertForbidden();

    expect($scoreType->refresh()->name)->not->toBe('Whatever I Like');
});

test('reordering the columns re-points what a worked-out column is worked out from', function () {
    $event = pairableEvent();
    $matchPoints = $event->scoreTypes()->where('slug', 'match-points')->firstOrFail();
    $victoryPoints = $event->scoreTypes()->where('slug', 'victory-points')->firstOrFail();
    $sportsmanship = EventScoreType::factory()->for($event)->create([
        'name' => 'Sportsmanship',
        'slug' => 'sportsmanship',
        'display_order' => 2,
    ]);

    $this->actingAs(organiserOf($event))
        ->putJson(route('events.score-types.replace', ['event' => $event->slug]), [
            'score_types' => [
                [
                    'id' => $sportsmanship->id,
                    'name' => 'Sportsmanship',
                    'sort_direction' => 'desc',
                    'is_derived' => false,
                    'is_primary' => false,
                    'counts_for_ranking' => false,
                ],
                [
                    'id' => $matchPoints->id,
                    'name' => 'Match Points',
                    'sort_direction' => 'desc',
                    'is_derived' => true,
                    'is_primary' => false,
                    'counts_for_ranking' => true,
                    'win_points' => 3,
                    'draw_points' => 1,
                    'loss_points' => 0,
                ],
                [
                    'id' => $victoryPoints->id,
                    'name' => 'Victory Points',
                    'sort_direction' => 'desc',
                    'is_derived' => false,
                    'is_primary' => true,
                    'counts_for_ranking' => true,
                ],
            ],
        ])
        ->assertSuccessful();

    // Match Points are computed from the first column a Player enters, in
    // display order — now Sportsmanship rather than Victory Points.
    $player = User::factory()->create();
    $mine = EventAttendee::factory()->for($event)->withMember($player)->create();
    $theirs = EventAttendee::factory()->for($event)->withMember()->create();
    $round = Round::factory()->for($event)->live()->create();
    $game = Game::factory()->for($round)->create();
    $game->attendees()->attach([$mine->id, $theirs->id]);

    $this->actingAs($player)
        ->postJson(route('events.games.result.store', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $mine->id => ['sportsmanship' => 3, 'victory-points' => 10],
                $theirs->id => ['sportsmanship' => 5, 'victory-points' => 90],
            ],
        ])
        ->assertSuccessful();

    $matchPointsFor = fn (EventAttendee $attendee) => (float) GameScore::query()
        ->where('event_attendee_id', $attendee->id)
        ->where('event_score_type_id', $matchPoints->id)
        ->value('value');

    // Won the sportsmanship, lost the victory points: the Match Points follow
    // the column that now leads.
    expect($matchPointsFor($theirs))->toBe(3.0)
        ->and($matchPointsFor($mine))->toBe(0.0);
});

test('a column the event did not have is added, and gets a slug of its own', function () {
    $event = Event::factory()->published()->create();
    EventScoreType::factory()->for($event)->create([
        'name' => 'Painting',
        'slug' => 'painting',
        'display_order' => 0,
    ]);
    $existing = $event->scoreTypes()->firstOrFail();

    $body = fn () => [
        'score_types' => [
            [
                'id' => $existing->id,
                'name' => 'Painting',
                'sort_direction' => 'desc',
                'is_derived' => false,
                'is_primary' => true,
                'counts_for_ranking' => true,
            ],
            [
                'name' => 'Painting',
                'sort_direction' => 'desc',
                'is_derived' => false,
                'is_primary' => false,
                'counts_for_ranking' => true,
            ],
        ],
    ];

    $this->actingAs(organiserOf($event))
        ->putJson(route('events.score-types.replace', ['event' => $event->slug]), $body())
        ->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.1.name', 'Painting')
        ->assertJsonPath('data.1.slug', 'painting-2')
        ->assertJsonPath('data.1.display_order', 1)
        ->assertJsonPath('data.1.ranking_order', 2)
        ->assertJsonPath('data.1.is_scored', false);

    expect($event->scoreTypes()->count())->toBe(2);
});

test('a column nobody has been scored on is dropped when it is left out', function () {
    $event = pairableEvent();
    $matchPoints = $event->scoreTypes()->where('slug', 'match-points')->firstOrFail();
    $spare = EventScoreType::factory()->for($event)->create(['name' => 'Painting', 'slug' => 'painting']);
    $victoryPoints = $event->scoreTypes()->where('slug', 'victory-points')->firstOrFail();

    $this->actingAs(organiserOf($event))
        ->putJson(route('events.score-types.replace', ['event' => $event->slug]), [
            'score_types' => [
                [
                    'id' => $matchPoints->id,
                    'name' => 'Match Points',
                    'sort_direction' => 'desc',
                    'is_derived' => true,
                    'is_primary' => false,
                    'counts_for_ranking' => true,
                    'win_points' => 3,
                    'draw_points' => 1,
                    'loss_points' => 0,
                ],
                [
                    'id' => $victoryPoints->id,
                    'name' => 'Victory Points',
                    'sort_direction' => 'desc',
                    'is_derived' => false,
                    'is_primary' => true,
                    'counts_for_ranking' => true,
                ],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');

    expect(EventScoreType::query()->whereKey($spare->id)->exists())->toBeFalse();
});

test('a column games have been scored on cannot be tidied away', function () {
    [$event] = submittedGame();
    $victoryPoints = $event->scoreTypes()->where('slug', 'victory-points')->firstOrFail();
    $painting = EventScoreType::factory()->for($event)->create(['name' => 'Painting', 'slug' => 'painting']);

    $this->actingAs(organiserOf($event))
        ->putJson(route('events.score-types.replace', ['event' => $event->slug]), [
            'score_types' => [[
                'id' => $painting->id,
                'name' => 'Painting',
                'sort_direction' => 'desc',
                'is_derived' => false,
                'is_primary' => true,
                'counts_for_ranking' => true,
            ]],
        ])
        ->assertJsonValidationErrors(['score_types' => 'Victory Points']);

    expect($victoryPoints->refresh()->scores()->count())->toBe(2)
        ->and($event->scoreTypes()->count())->toBe(2);
});

test('a column carries the heading an organiser wrote for it', function () {
    $event = pairableEvent();
    $victoryPoints = $event->scoreTypes()->where('slug', 'victory-points')->firstOrFail();

    $this->actingAs(organiserOf($event))
        ->putJson(route('events.score-types.replace', ['event' => $event->slug]), [
            'score_types' => [[
                'id' => $victoryPoints->id,
                'name' => 'Victory Points',
                'abbreviation' => 'VPs',
                'sort_direction' => 'desc',
                'is_derived' => false,
                'is_primary' => true,
                'counts_for_ranking' => true,
            ]],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.0.abbreviation', 'VPs');

    expect($victoryPoints->refresh()->abbreviation)->toBe('VPs');
});

test('a column left without a heading is given the initials of its name', function () {
    $event = pairableEvent();
    $victoryPoints = $event->scoreTypes()->where('slug', 'victory-points')->firstOrFail();

    $this->actingAs(organiserOf($event))
        ->putJson(route('events.score-types.replace', ['event' => $event->slug]), [
            'score_types' => [
                [
                    'id' => $victoryPoints->id,
                    'name' => 'Victory Points',
                    'abbreviation' => '   ',
                    'sort_direction' => 'desc',
                    'is_derived' => false,
                    'is_primary' => true,
                    'counts_for_ranking' => true,
                ],
                [
                    // A column being added, with nothing said about its
                    // heading at all.
                    'name' => 'Sportsmanship',
                    'sort_direction' => 'desc',
                    'is_derived' => false,
                    'is_primary' => false,
                    'counts_for_ranking' => false,
                ],
            ],
        ])
        ->assertSuccessful()
        // A multi-word name gives its initials; a single-word one is cut to
        // three letters, since one letter says nothing.
        ->assertJsonPath('data.0.abbreviation', 'VP')
        ->assertJsonPath('data.1.abbreviation', 'SPO');
});

test('a heading longer than a table column has room for is refused', function () {
    $event = pairableEvent();
    $victoryPoints = $event->scoreTypes()->where('slug', 'victory-points')->firstOrFail();

    $this->actingAs(organiserOf($event))
        ->putJson(route('events.score-types.replace', ['event' => $event->slug]), [
            'score_types' => [[
                'id' => $victoryPoints->id,
                'name' => 'Victory Points',
                'abbreviation' => 'Victory Points Total',
                'sort_direction' => 'desc',
                'is_derived' => false,
                'is_primary' => true,
                'counts_for_ranking' => true,
            ]],
        ])
        ->assertJsonValidationErrors(['score_types.0.abbreviation']);
});
