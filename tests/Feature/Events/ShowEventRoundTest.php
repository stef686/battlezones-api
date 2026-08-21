<?php

use App\Enums\EventOrganiserRole;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventScoreType;
use App\Models\Faction;
use App\Models\Game;
use App\Models\GameScore;
use App\Models\Round;
use App\Models\User;

test('it returns round detail with games ordered by table number', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create(['number' => 1, 'name' => 'Round One']);

    $vp = EventScoreType::factory()->victoryPoints()->for($event)->create();

    $faction = Faction::factory()->create(['name' => 'Space Marines']);
    $user1 = User::factory()->create(['name' => 'Alice']);
    $user2 = User::factory()->create(['name' => 'Bob']);

    $attendee1 = EventAttendee::factory()->for($event)->withMember($user1, ['faction_id' => $faction->id])->create();
    $attendee2 = EventAttendee::factory()->for($event)->withMember($user2)->create();

    $game2 = Game::factory()->for($round)->create(['table_number' => 2]);
    $game1 = Game::factory()->for($round)->create(['table_number' => 1]);

    $game1->attendees()->attach($attendee1);
    $game1->attendees()->attach($attendee2);
    $game2->attendees()->attach($attendee1);

    GameScore::factory()->create(['game_id' => $game1->id, 'event_attendee_id' => $attendee1->id, 'event_score_type_id' => $vp->id, 'value' => 85]);
    GameScore::factory()->create(['game_id' => $game1->id, 'event_attendee_id' => $attendee2->id, 'event_score_type_id' => $vp->id, 'value' => 70]);

    $response = $this->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertSuccessful();

    expect($response->json('data.id'))->toBe($round->id)
        ->and($response->json('data.number'))->toBe(1)
        ->and($response->json('data.name'))->toBe('Round One')
        ->and($response->json('data.games'))->toHaveCount(2);

    $firstGame = $response->json('data.games.0');
    expect($firstGame['table_number'])->toBe(1)
        ->and($firstGame['is_bye'])->toBeFalse()
        ->and($firstGame['attendees'])->toHaveCount(2)
        ->and($firstGame['attendees'][0]['name'])->toBe('Alice')
        ->and($firstGame['attendees'][0]['members'][0]['name'])->toBe('Alice')
        ->and($firstGame['attendees'][0]['members'][0]['faction']['name'])->toBe('Space Marines')
        ->and($firstGame['attendees'][0]['scores'])->toBe(['victory-points' => '85.00']);
});

test('it returns 404 if round does not belong to event', function () {
    $event = Event::factory()->active()->create();
    $otherEvent = Event::factory()->active()->create();
    $round = Round::factory()->for($otherEvent)->create();

    $this->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertNotFound();
});

test('it returns 404 for non-publicly-visible events', function (string $state) {
    $event = Event::factory()->{$state}()->create();
    $round = Round::factory()->for($event)->create();

    $this->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertNotFound();
})->with(['draft', 'cancelled']);

test('it returns 404 for published events (no rounds visible)', function () {
    $event = Event::factory()->published()->create();
    $round = Round::factory()->for($event)->create();

    $this->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertNotFound();
});

test('it returns 404 for a draft round to players', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->create(['number' => 1]);

    $this->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertNotFound();
});

test('it returns a draft round to organisers', function () {
    $event = Event::factory()->active()->create();
    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);
    $round = Round::factory()->for($event)->create(['number' => 1]);

    $this->actingAs($organiser)
        ->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'draft');
});
