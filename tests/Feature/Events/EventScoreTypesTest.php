<?php

use App\Models\Event;
use App\Models\EventScoreType;
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
