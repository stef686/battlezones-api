<?php

use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventScoreType;
use App\Models\Game;
use App\Models\GameScore;
use App\Models\Round;

test('it decides a winner on a game whose relations nobody loaded', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create(['number' => 1]);

    $vp = EventScoreType::factory()->victoryPoints()->rankedAt(1)->for($event)->create(['display_order' => 0]);

    $winner = EventAttendee::factory()->for($event)->create();
    $loser = EventAttendee::factory()->for($event)->create();

    $game = Game::factory()->for($round)->create(['table_number' => 1]);
    $game->attendees()->attach([$winner->id, $loser->id]);

    GameScore::factory()->create(['game_id' => $game->id, 'event_attendee_id' => $winner->id, 'event_score_type_id' => $vp->id, 'value' => 85]);
    GameScore::factory()->create(['game_id' => $game->id, 'event_attendee_id' => $loser->id, 'event_score_type_id' => $vp->id, 'value' => 70]);

    // Fetched fresh, with nothing eager loaded: a caller that forgets the
    // loads gets the right answer rather than a lazy-loading exception.
    $fresh = Game::query()->findOrFail($game->id);

    expect($fresh->winningAttendeeId($event->scoreTypes()->get()))->toBe($winner->id);
});

test('it leads a listing with the score type an organiser marked, however they are ordered', function () {
    $event = Event::factory()->active()->create();

    $mp = EventScoreType::factory()->matchPoints()->for($event)->create(['display_order' => 0, 'is_primary' => true]);
    EventScoreType::factory()->victoryPoints()->for($event)->create(['display_order' => 1]);

    $ordered = $event->scoreTypes()->orderBy('display_order')->get();

    // Passing the columns in already ordered is the same answer as letting
    // the Event sort them itself, and one sort rather than two.
    expect($event->primaryScoreType($ordered)?->id)->toBe($mp->id)
        ->and($event->primaryScoreType()?->id)->toBe($mp->id);
});

test('it leads with the score played for at the table where nobody marked one', function () {
    $event = Event::factory()->active()->create();

    EventScoreType::factory()->matchPoints()->for($event)->create(['display_order' => 0]);
    $vp = EventScoreType::factory()->victoryPoints()->for($event)->create(['display_order' => 1, 'is_derived' => false]);

    expect($event->primaryScoreType()?->id)->toBe($vp->id);
});
