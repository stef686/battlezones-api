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

test('it returns game detail with attendees, scores and army lists', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create(['number' => 1]);

    $vp = EventScoreType::factory()->victoryPoints()->for($event)->create(['display_order' => 0]);
    $mp = EventScoreType::factory()->matchPoints()->rankedAt(1)->for($event)->create(['display_order' => 1]);

    $faction = Faction::factory()->create(['name' => 'Necrons']);
    $user1 = User::factory()->create(['name' => 'Alice']);
    $user2 = User::factory()->create(['name' => 'Bob']);

    $attendee1 = EventAttendee::factory()->for($event)
        ->withMember($user1, ['faction_id' => $faction->id, 'army_list' => '2000pts Necrons'])
        ->create();
    $attendee2 = EventAttendee::factory()->for($event)
        ->withMember($user2, ['army_list' => '2000pts Tyranids'])
        ->create();

    $game = Game::factory()->for($round)->create(['table_number' => 3]);
    $game->attendees()->attach($attendee1);
    $game->attendees()->attach($attendee2);

    GameScore::factory()->create(['game_id' => $game->id, 'event_attendee_id' => $attendee1->id, 'event_score_type_id' => $vp->id, 'value' => 85]);
    GameScore::factory()->create(['game_id' => $game->id, 'event_attendee_id' => $attendee2->id, 'event_score_type_id' => $vp->id, 'value' => 70]);
    GameScore::factory()->create(['game_id' => $game->id, 'event_attendee_id' => $attendee1->id, 'event_score_type_id' => $mp->id, 'value' => 3]);
    GameScore::factory()->create(['game_id' => $game->id, 'event_attendee_id' => $attendee2->id, 'event_score_type_id' => $mp->id, 'value' => 0]);

    $attendee1->memberships()->update(['army_list_submitted_at' => now()]);

    $response = $this->actingAs($user2)
        ->getJson(route('events.games.show', ['event' => $event->slug, 'game' => $game->id]))
        ->assertSuccessful();

    expect($response->json('data.id'))->toBe($game->id)
        ->and($response->json('data.table_number'))->toBe(3)
        ->and($response->json('data.is_bye'))->toBeFalse()
        ->and($response->json('data.round.number'))->toBe(1)
        ->and($response->json('data.attendees'))->toHaveCount(2)
        ->and($response->json('data.attendees.0.members.0.name'))->toBe('Alice')
        ->and($response->json('data.attendees.0.members.0.faction.name'))->toBe('Necrons')
        ->and($response->json('data.attendees.0.members.0.army_list'))->toBe('2000pts Necrons')
        ->and($response->json('data.attendees.0.scores.victory-points'))->toBe('85.00')
        ->and($response->json('data.attendees.0.scores.match-points'))->toBe('3.00')
        ->and($response->json('data.attendees.1.scores.victory-points'))->toBe('70.00')
        ->and($response->json('data.attendees.1.scores.match-points'))->toBe('0.00');
});

test('it validates game belongs to event through round', function () {
    $event = Event::factory()->active()->create();
    $otherEvent = Event::factory()->active()->create();
    $round = Round::factory()->for($otherEvent)->create();
    $game = Game::factory()->for($round)->create();

    $this->getJson(route('events.games.show', ['event' => $event->slug, 'game' => $game->id]))
        ->assertNotFound();
});

test('it returns 404 for non-publicly-visible events', function (string $state) {
    $event = Event::factory()->{$state}()->create();
    $round = Round::factory()->for($event)->create();
    $game = Game::factory()->for($round)->create();

    $this->getJson(route('events.games.show', ['event' => $event->slug, 'game' => $game->id]))
        ->assertNotFound();
})->with(['draft', 'cancelled']);

test('bye game with single attendee', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create();
    $attendee = EventAttendee::factory()->for($event)->withMember()->create();

    $game = Game::factory()->for($round)->bye()->create(['table_number' => null]);
    $game->attendees()->attach($attendee);

    $response = $this->getJson(route('events.games.show', ['event' => $event->slug, 'game' => $game->id]))
        ->assertSuccessful();

    expect($response->json('data.is_bye'))->toBeTrue()
        ->and($response->json('data.table_number'))->toBeNull()
        ->and($response->json('data.attendees'))->toHaveCount(1);
});

test('multiplayer game with more than two attendees', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create();

    $game = Game::factory()->for($round)->create(['table_number' => 5]);

    foreach (['Alice', 'Bob', 'Charlie', 'Diana'] as $name) {
        $attendee = EventAttendee::factory()->for($event)
            ->withMember(User::factory()->create(['name' => $name]))
            ->create();
        $game->attendees()->attach($attendee);
    }

    $response = $this->getJson(route('events.games.show', ['event' => $event->slug, 'game' => $game->id]))
        ->assertSuccessful();

    expect($response->json('data.attendees'))->toHaveCount(4);
});

test('it is a public endpoint requiring no auth', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create();
    $game = Game::factory()->for($round)->create();

    $this->getJson(route('events.games.show', ['event' => $event->slug, 'game' => $game->id]))
        ->assertSuccessful();
});

test('it returns 404 for a game in a draft round to players', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->create();
    $game = Game::factory()->for($round)->create();

    $this->getJson(route('events.games.show', ['event' => $event->slug, 'game' => $game->id]))
        ->assertNotFound();
});

test('it returns a game in a draft round to organisers', function () {
    $event = Event::factory()->active()->create();
    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);
    $round = Round::factory()->for($event)->create();
    $game = Game::factory()->for($round)->create();

    $this->actingAs($organiser)
        ->getJson(route('events.games.show', ['event' => $event->slug, 'game' => $game->id]))
        ->assertSuccessful();
});
