<?php

use App\Enums\EventOrganiserRole;
use App\Models\Event;
use App\Models\Round;
use App\Models\User;

test('it returns rounds for a completed event', function () {
    $event = Event::factory()->completed()->create();
    Round::factory()->for($event)->live()->create(['number' => 1]);

    $this->getJson(route('events.rounds.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('it returns 404 for events without rounds visibility', function (string $state) {
    $event = Event::factory()->{$state}()->create();
    Round::factory()->for($event)->live()->create(['number' => 1]);

    $this->getJson(route('events.rounds.index', ['event' => $event->slug]))
        ->assertNotFound();
})->with(['draft', 'published', 'cancelled']);

test('it returns rounds ordered by number for an active event', function () {
    $event = Event::factory()->active()->create();

    Round::factory()->for($event)->live()->create(['number' => 3]);
    Round::factory()->for($event)->live()->create(['number' => 1]);
    Round::factory()->for($event)->live()->create(['number' => 2]);

    $response = $this->getJson(route('events.rounds.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');

    expect(collect($response->json('data'))->pluck('number')->all())
        ->toEqual([1, 2, 3]);
});

test('it does not leak rounds from other events', function () {
    $event = Event::factory()->active()->create();
    $otherEvent = Event::factory()->active()->create();

    Round::factory()->for($event)->live()->create(['number' => 1]);
    Round::factory()->for($otherEvent)->count(3)->create();

    $this->getJson(route('events.rounds.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('it is a public endpoint requiring no auth', function () {
    $event = Event::factory()->active()->create();

    $this->getJson(route('events.rounds.index', ['event' => $event->slug]))
        ->assertSuccessful();
});

test('it returns 404 for a nonexistent event slug', function () {
    $this->getJson(route('events.rounds.index', ['event' => 'does-not-exist']))
        ->assertNotFound();
});

test('it hides draft rounds from players', function () {
    $event = Event::factory()->active()->create();

    Round::factory()->for($event)->live()->create(['number' => 1]);
    Round::factory()->for($event)->create(['number' => 2]);

    $response = $this->getJson(route('events.rounds.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');

    expect($response->json('data.0.number'))->toBe(1);
});

test('it shows draft rounds to organisers', function () {
    $event = Event::factory()->active()->create();
    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    Round::factory()->for($event)->live()->create(['number' => 1]);
    Round::factory()->for($event)->create(['number' => 2]);

    $this->actingAs($organiser)
        ->getJson(route('events.rounds.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});
