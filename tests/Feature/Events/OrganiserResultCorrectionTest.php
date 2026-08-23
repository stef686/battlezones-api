<?php

use App\Enums\EventOrganiserRole;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventScoreType;
use App\Models\Game;
use App\Models\GameScore;
use App\Models\Round;
use App\Models\User;

test('an organiser corrects a submitted result and the correction is attributed', function () {
    [$event, $game, $mine, $theirs, $player] = submittedGame();

    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    $this->actingAs($organiser)
        ->putJson(route('events.games.result.update', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $mine->id => ['victory-points' => 70],
                $theirs->id => ['victory-points' => 85],
            ],
        ])
        ->assertSuccessful();

    $game->refresh();

    expect($game->edited_by_user_id)->toBe($organiser->id)
        ->and($game->edited_at)->not->toBeNull()
        ->and($game->submitted_by_user_id)->toBe($player->id)
        ->and(GameScore::query()->where('game_id', $game->id)->where('event_attendee_id', $mine->id)->where('value', '70.00')->exists())->toBeTrue();
});

test('a player cannot edit a result', function () {
    [$event, $game, $mine, $theirs, $player] = submittedGame();

    $this->actingAs($player)
        ->putJson(route('events.games.result.update', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $mine->id => ['victory-points' => 70],
                $theirs->id => ['victory-points' => 85],
            ],
        ])
        ->assertForbidden();
});

test('an organiser can edit a result in a draft round', function () {
    $event = Event::factory()->active()->create();
    EventScoreType::factory()->victoryPoints()->rankedAt(1)->for($event)->create();

    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    $home = EventAttendee::factory()->for($event)->withMember()->create();
    $away = EventAttendee::factory()->for($event)->withMember()->create();

    $round = Round::factory()->for($event)->create();
    $game = Game::factory()->for($round)->create();
    $game->attendees()->attach([$home->id, $away->id]);

    $this->actingAs($organiser)
        ->putJson(route('events.games.result.update', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $home->id => ['victory-points' => 60],
                $away->id => ['victory-points' => 40],
            ],
        ])
        ->assertSuccessful();

    expect(GameScore::query()->where('game_id', $game->id)->count())->toBe(2);
});

test('an organiser cannot supply derived score types when editing', function () {
    [$event, $game, $mine, $theirs] = submittedGame();

    $matchPoints = EventScoreType::factory()->matchPoints()->rankedAt(1)->for($event)->create(['display_order' => 0]);

    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    $this->actingAs($organiser)
        ->putJson(route('events.games.result.update', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $mine->id => ['victory-points' => 70, 'match-points' => 3],
                $theirs->id => ['victory-points' => 85, 'match-points' => 0],
            ],
        ])
        ->assertStatus(422);

    expect($matchPoints->slug)->toBe('match-points')
        ->and($game->refresh()->edited_at)->toBeNull();
});

test('editing a result leaves an open flag open', function () {
    [$event, $game, $mine, $theirs, $player] = submittedGame();

    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    $this->actingAs($player)
        ->postJson(route('events.games.flag.store', ['event' => $event->slug, 'game' => $game->id]))
        ->assertSuccessful();

    $this->actingAs($organiser)
        ->putJson(route('events.games.result.update', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $mine->id => ['victory-points' => 70],
                $theirs->id => ['victory-points' => 85],
            ],
        ])
        ->assertSuccessful();

    $this->actingAs($organiser)
        ->getJson(route('events.flags.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('edit attribution is visible on the game', function () {
    [$event, $game, $mine, $theirs, $player] = submittedGame();

    $organiser = User::factory()->create(['name' => 'Tournament Organiser']);
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    $this->actingAs($organiser)
        ->putJson(route('events.games.result.update', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $mine->id => ['victory-points' => 70],
                $theirs->id => ['victory-points' => 85],
            ],
        ])
        ->assertSuccessful();

    $response = $this->actingAs($player)
        ->getJson(route('events.games.show', ['event' => $event->slug, 'game' => $game->id]))
        ->assertSuccessful();

    expect($response->json('data.result.submitted_by.id'))->toBe($player->id)
        ->and($response->json('data.result.edited_by.name'))->toBe('Tournament Organiser')
        ->and($response->json('data.result.edited_at'))->not->toBeNull();
});

test('standings reflect an edited result immediately', function () {
    [$event, $game, $mine, $theirs] = submittedGame();

    $event->update(['settings' => $event->settings->with(['standings_visible' => true])]);

    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    $this->getJson(route('events.standings.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonPath('data.0.attendee.id', $mine->id);

    $this->actingAs($organiser)
        ->putJson(route('events.games.result.update', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $mine->id => ['victory-points' => 70],
                $theirs->id => ['victory-points' => 85],
            ],
        ])
        ->assertSuccessful();

    $this->getJson(route('events.standings.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonPath('data.0.attendee.id', $theirs->id);
});
