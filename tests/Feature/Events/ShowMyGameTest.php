<?php

use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\Game;
use App\Models\Round;
use App\Models\User;

test('a player sees their game and table number in the current round', function () {
    $event = Event::factory()->active()->create();
    $player = User::factory()->create();
    $attendee = EventAttendee::factory()->for($event)->withMember($player)->create();

    $roundOne = Round::factory()->for($event)->live()->create(['number' => 1]);
    $roundTwo = Round::factory()->for($event)->live()->create(['number' => 2]);

    $oldGame = Game::factory()->for($roundOne)->create(['table_number' => 9]);
    $oldGame->attendees()->attach($attendee);

    $game = Game::factory()->for($roundTwo)->create(['table_number' => 4]);
    $game->attendees()->attach($attendee);

    $response = $this->actingAs($player)
        ->getJson(route('events.my-game.show', ['event' => $event->slug]))
        ->assertSuccessful();

    expect($response->json('data.id'))->toBe($game->id)
        ->and($response->json('data.table_number'))->toBe(4)
        ->and($response->json('data.round.number'))->toBe(2);
});

test('it returns nothing while the round is still draft', function () {
    $event = Event::factory()->active()->create();
    $player = User::factory()->create();
    $attendee = EventAttendee::factory()->for($event)->withMember($player)->create();

    $round = Round::factory()->for($event)->create(['number' => 1]);
    $game = Game::factory()->for($round)->create(['table_number' => 4]);
    $game->attendees()->attach($attendee);

    $this->actingAs($player)
        ->getJson(route('events.my-game.show', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonPath('data', null);
});

test('it returns nothing for a player with no game in the current round', function () {
    $event = Event::factory()->active()->create();
    $player = User::factory()->create();
    EventAttendee::factory()->for($event)->withMember($player)->create();

    Round::factory()->for($event)->live()->create(['number' => 1]);

    $this->actingAs($player)
        ->getJson(route('events.my-game.show', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonPath('data', null);
});

test('it requires authentication', function () {
    $event = Event::factory()->active()->create();

    $this->getJson(route('events.my-game.show', ['event' => $event->slug]))
        ->assertUnauthorized();
});
