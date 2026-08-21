<?php

use App\Enums\EventOrganiserRole;
use App\Enums\RoundStatus;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventScoreType;
use App\Models\Game;
use App\Models\GameScore;
use App\Models\Round;
use App\Models\User;

function organiserOf(Event $event): User
{
    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    return $organiser;
}

test('an organiser publishes a round and its pairings become visible', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->create(['number' => 1]);
    Game::factory()->for($round)->create(['table_number' => 4]);

    $this->actingAs(organiserOf($event))
        ->postJson(route('events.rounds.publish', ['event' => $event->slug, 'round' => $round->id]))
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'live');

    expect($round->refresh()->status)->toBe(RoundStatus::Live);

    $response = $this->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertSuccessful();

    expect($response->json('data.games.0.table_number'))->toBe(4);
});

test('earlier rounds stay live when a later one publishes', function () {
    $event = Event::factory()->active()->create();
    $roundOne = Round::factory()->for($event)->live()->create(['number' => 1]);
    $roundTwo = Round::factory()->for($event)->create(['number' => 2]);

    $this->actingAs(organiserOf($event))
        ->postJson(route('events.rounds.publish', ['event' => $event->slug, 'round' => $roundTwo->id]))
        ->assertSuccessful();

    expect($roundOne->refresh()->status)->toBe(RoundStatus::Live)
        ->and($roundTwo->refresh()->status)->toBe(RoundStatus::Live)
        ->and($event->currentRound()?->id)->toBe($roundTwo->id);
});

test('a player may not publish a round', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->create(['number' => 1]);

    $this->actingAs(User::factory()->create())
        ->postJson(route('events.rounds.publish', ['event' => $event->slug, 'round' => $round->id]))
        ->assertForbidden();

    expect($round->refresh()->status)->toBe(RoundStatus::Draft);
});

test('the current round is the highest-numbered live round', function () {
    $event = Event::factory()->active()->create();
    Round::factory()->for($event)->live()->create(['number' => 1]);
    $roundTwo = Round::factory()->for($event)->live()->create(['number' => 2]);
    Round::factory()->for($event)->create(['number' => 3]);

    expect($event->currentRound()?->id)->toBe($roundTwo->id);
});

test('there is no current round before anything is published', function () {
    $event = Event::factory()->active()->create();
    Round::factory()->for($event)->create(['number' => 1]);

    expect($event->currentRound())->toBeNull();
});

test('an organiser unpublishes a round while it has no results', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create(['number' => 1]);
    Game::factory()->for($round)->create(['table_number' => 4]);

    $this->actingAs(organiserOf($event))
        ->deleteJson(route('events.rounds.unpublish', ['event' => $event->slug, 'round' => $round->id]))
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'draft');

    expect($round->refresh()->status)->toBe(RoundStatus::Draft);

    auth()->forgetGuards();

    $this->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertNotFound();
});

test('unpublishing is rejected once any result exists', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create(['number' => 1]);
    $game = Game::factory()->for($round)->create(['table_number' => 4]);
    $attendee = EventAttendee::factory()->for($event)->withMember()->create();
    $game->attendees()->attach($attendee);

    GameScore::factory()->create([
        'game_id' => $game->id,
        'event_attendee_id' => $attendee->id,
        'event_score_type_id' => EventScoreType::factory()->victoryPoints()->for($event)->create()->id,
        'value' => 85,
    ]);

    $this->actingAs(organiserOf($event))
        ->deleteJson(route('events.rounds.unpublish', ['event' => $event->slug, 'round' => $round->id]))
        ->assertStatus(422);

    expect($round->refresh()->status)->toBe(RoundStatus::Live);
});

test('a player may not unpublish a round', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create(['number' => 1]);

    $this->actingAs(User::factory()->create())
        ->deleteJson(route('events.rounds.unpublish', ['event' => $event->slug, 'round' => $round->id]))
        ->assertForbidden();
});
