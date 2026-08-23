<?php

use App\Events\ResultSubmitted;
use App\Models\Event as EventModel;
use App\Models\EventAttendee;
use App\Models\EventScoreType;
use App\Models\Game;
use App\Models\GameScore;
use App\Models\Round;
use App\Models\User;
use Illuminate\Support\Facades\Event;

test('a player submits victory points for both attendees in their game', function () {
    $event = EventModel::factory()->active()->create();
    $victoryPoints = EventScoreType::factory()->victoryPoints()->for($event)->create();

    $player = User::factory()->create();
    $mine = EventAttendee::factory()->for($event)->withMember($player)->create();
    $theirs = EventAttendee::factory()->for($event)->withMember()->create();

    $round = Round::factory()->for($event)->live()->create();
    $game = Game::factory()->for($round)->create(['table_number' => 4]);
    $game->attendees()->attach([$mine->id, $theirs->id]);

    $this->actingAs($player)
        ->postJson(route('events.games.result.store', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $mine->id => ['victory-points' => 85],
                $theirs->id => ['victory-points' => 70],
            ],
        ])
        ->assertSuccessful();

    expect(GameScore::query()->where('game_id', $game->id)->where('event_attendee_id', $mine->id)->value('value'))->toBe('85.00')
        ->and(GameScore::query()->where('game_id', $game->id)->where('event_attendee_id', $theirs->id)->value('value'))->toBe('70.00');
});

test('it records who submitted the result and when', function () {
    $event = EventModel::factory()->active()->create();
    EventScoreType::factory()->victoryPoints()->for($event)->create();

    $player = User::factory()->create();
    $mine = EventAttendee::factory()->for($event)->withMember($player)->create();
    $theirs = EventAttendee::factory()->for($event)->withMember()->create();

    $round = Round::factory()->for($event)->live()->create();
    $game = Game::factory()->for($round)->create();
    $game->attendees()->attach([$mine->id, $theirs->id]);

    $this->actingAs($player)
        ->postJson(route('events.games.result.store', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $mine->id => ['victory-points' => 85],
                $theirs->id => ['victory-points' => 70],
            ],
        ])
        ->assertSuccessful();

    $game->refresh();

    expect($game->submitted_by_user_id)->toBe($player->id)
        ->and($game->submitted_at)->not->toBeNull();
});

test('a contradicting second submission is rejected and the first result stands', function () {
    $event = EventModel::factory()->active()->create();
    EventScoreType::factory()->victoryPoints()->for($event)->create();

    $player = User::factory()->create();
    $opponent = User::factory()->create();
    $mine = EventAttendee::factory()->for($event)->withMember($player)->create();
    $theirs = EventAttendee::factory()->for($event)->withMember($opponent)->create();

    $round = Round::factory()->for($event)->live()->create();
    $game = Game::factory()->for($round)->create();
    $game->attendees()->attach([$mine->id, $theirs->id]);

    $url = route('events.games.result.store', ['event' => $event->slug, 'game' => $game->id]);

    $this->actingAs($player)
        ->postJson($url, [
            'scores' => [
                $mine->id => ['victory-points' => 85],
                $theirs->id => ['victory-points' => 70],
            ],
        ])
        ->assertSuccessful();

    $this->actingAs($opponent)
        ->postJson($url, [
            'scores' => [
                $mine->id => ['victory-points' => 10],
                $theirs->id => ['victory-points' => 99],
            ],
        ])
        ->assertStatus(409)
        ->assertJsonPath('message', 'A result has already been submitted for this game. Flag it if it needs correcting.');

    $game->refresh();

    expect($game->submitted_by_user_id)->toBe($player->id)
        ->and(GameScore::query()->where('game_id', $game->id)->where('event_attendee_id', $mine->id)->value('value'))->toBe('85.00')
        ->and(GameScore::query()->where('game_id', $game->id)->where('event_attendee_id', $theirs->id)->value('value'))->toBe('70.00');
});

test('it rejects client-supplied match points', function () {
    $event = EventModel::factory()->active()->create();
    EventScoreType::factory()->victoryPoints()->for($event)->create();
    EventScoreType::factory()->matchPoints()->rankedAt(1)->for($event)->create();

    $player = User::factory()->create();
    $mine = EventAttendee::factory()->for($event)->withMember($player)->create();
    $theirs = EventAttendee::factory()->for($event)->withMember()->create();

    $round = Round::factory()->for($event)->live()->create();
    $game = Game::factory()->for($round)->create();
    $game->attendees()->attach([$mine->id, $theirs->id]);

    $this->actingAs($player)
        ->postJson(route('events.games.result.store', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $mine->id => ['victory-points' => 85, 'match-points' => 3],
                $theirs->id => ['victory-points' => 70, 'match-points' => 0],
            ],
        ])
        ->assertStatus(422);

    $game->refresh();

    expect($game->hasResult())->toBeFalse()
        ->and(GameScore::query()->where('game_id', $game->id)->exists())->toBeFalse();
});

test('it derives match points from the submitted victory points', function () {
    $event = EventModel::factory()->active()->create();
    EventScoreType::factory()->victoryPoints()->for($event)->create();
    $matchPoints = EventScoreType::factory()->matchPoints(win: 3, draw: 1, loss: 0)->rankedAt(1)->for($event)->create();

    $player = User::factory()->create();
    $mine = EventAttendee::factory()->for($event)->withMember($player)->create();
    $theirs = EventAttendee::factory()->for($event)->withMember()->create();

    $round = Round::factory()->for($event)->live()->create();
    $game = Game::factory()->for($round)->create();
    $game->attendees()->attach([$mine->id, $theirs->id]);

    $this->actingAs($player)
        ->postJson(route('events.games.result.store', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $mine->id => ['victory-points' => 85],
                $theirs->id => ['victory-points' => 70],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.attendees.0.scores.match-points', '3.00');

    expect(GameScore::query()->where('game_id', $game->id)->where('event_attendee_id', $theirs->id)->where('event_score_type_id', $matchPoints->id)->value('value'))->toBe('0.00');
});

test('submission is rejected while the round is still a draft', function () {
    $event = EventModel::factory()->active()->create();
    EventScoreType::factory()->victoryPoints()->for($event)->create();

    $player = User::factory()->create();
    $mine = EventAttendee::factory()->for($event)->withMember($player)->create();
    $theirs = EventAttendee::factory()->for($event)->withMember()->create();

    $round = Round::factory()->for($event)->create();
    $game = Game::factory()->for($round)->create();
    $game->attendees()->attach([$mine->id, $theirs->id]);

    $this->actingAs($player)
        ->postJson(route('events.games.result.store', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $mine->id => ['victory-points' => 85],
                $theirs->id => ['victory-points' => 70],
            ],
        ])
        ->assertNotFound();

    expect(GameScore::query()->where('game_id', $game->id)->exists())->toBeFalse();
});

test('a player who is not in the game cannot submit its result', function () {
    $event = EventModel::factory()->active()->create();
    EventScoreType::factory()->victoryPoints()->for($event)->create();

    $outsider = User::factory()->create();
    EventAttendee::factory()->for($event)->withMember($outsider)->create();

    $one = EventAttendee::factory()->for($event)->withMember()->create();
    $two = EventAttendee::factory()->for($event)->withMember()->create();

    $round = Round::factory()->for($event)->live()->create();
    $game = Game::factory()->for($round)->create();
    $game->attendees()->attach([$one->id, $two->id]);

    $this->actingAs($outsider)
        ->postJson(route('events.games.result.store', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $one->id => ['victory-points' => 85],
                $two->id => ['victory-points' => 70],
            ],
        ])
        ->assertForbidden();

    expect(GameScore::query()->where('game_id', $game->id)->exists())->toBeFalse();
});

test('it requires authentication', function () {
    $event = EventModel::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create();
    $game = Game::factory()->for($round)->create();

    $this->postJson(route('events.games.result.store', ['event' => $event->slug, 'game' => $game->id]), [])
        ->assertUnauthorized();
});

test('a game belonging to another event is not found', function () {
    $event = EventModel::factory()->active()->create();
    $otherEvent = EventModel::factory()->active()->create();
    EventScoreType::factory()->victoryPoints()->for($otherEvent)->create();

    $player = User::factory()->create();
    $mine = EventAttendee::factory()->for($otherEvent)->withMember($player)->create();
    $theirs = EventAttendee::factory()->for($otherEvent)->withMember()->create();

    $round = Round::factory()->for($otherEvent)->live()->create();
    $game = Game::factory()->for($round)->create();
    $game->attendees()->attach([$mine->id, $theirs->id]);

    $this->actingAs($player)
        ->postJson(route('events.games.result.store', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $mine->id => ['victory-points' => 85],
                $theirs->id => ['victory-points' => 70],
            ],
        ])
        ->assertNotFound();
});

test('it fires a result submitted event', function () {
    Event::fake([ResultSubmitted::class]);

    $event = EventModel::factory()->active()->create();
    EventScoreType::factory()->victoryPoints()->for($event)->create();

    $player = User::factory()->create();
    $mine = EventAttendee::factory()->for($event)->withMember($player)->create();
    $theirs = EventAttendee::factory()->for($event)->withMember()->create();

    $round = Round::factory()->for($event)->live()->create();
    $game = Game::factory()->for($round)->create();
    $game->attendees()->attach([$mine->id, $theirs->id]);

    $this->actingAs($player)
        ->postJson(route('events.games.result.store', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $mine->id => ['victory-points' => 85],
                $theirs->id => ['victory-points' => 70],
            ],
        ])
        ->assertSuccessful();

    Event::assertDispatched(ResultSubmitted::class, fn (ResultSubmitted $dispatched): bool => $dispatched->game->is($game)
        && $dispatched->submittedBy->is($player));
});

test('a bye has no result to submit', function () {
    $event = EventModel::factory()->active()->create();
    EventScoreType::factory()->victoryPoints()->for($event)->create();

    $player = User::factory()->create();
    $mine = EventAttendee::factory()->for($event)->withMember($player)->create();

    $round = Round::factory()->for($event)->live()->create();
    $game = Game::factory()->for($round)->bye()->create();
    $game->attendees()->attach([$mine->id]);

    $this->actingAs($player)
        ->postJson(route('events.games.result.store', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [$mine->id => ['victory-points' => 85]],
        ])
        ->assertForbidden();
});

test('it rejects a submission that misses an attendee', function () {
    $event = EventModel::factory()->active()->create();
    EventScoreType::factory()->victoryPoints()->for($event)->create();

    $player = User::factory()->create();
    $mine = EventAttendee::factory()->for($event)->withMember($player)->create();
    $theirs = EventAttendee::factory()->for($event)->withMember()->create();

    $round = Round::factory()->for($event)->live()->create();
    $game = Game::factory()->for($round)->create();
    $game->attendees()->attach([$mine->id, $theirs->id]);

    $this->actingAs($player)
        ->postJson(route('events.games.result.store', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [$mine->id => ['victory-points' => 85]],
        ])
        ->assertJsonValidationErrors('scores');
});

test('the conflict response carries the stored result, so a lost response is not mistaken for a dispute', function () {
    [$event, $game, $mine, $theirs, $player] = submittedGame();

    $url = route('events.games.result.store', ['event' => $event->slug, 'game' => $game->id]);

    // The Player's first submission landed; only its response was lost, so the
    // retry looks exactly like the request that succeeded.
    $response = $this->actingAs($player)
        ->postJson($url, [
            'scores' => [
                $mine->id => ['victory-points' => 85],
                $theirs->id => ['victory-points' => 70],
            ],
        ])
        ->assertStatus(409);

    $response
        ->assertJsonPath('message', 'A result has already been submitted for this game. Flag it if it needs correcting.')
        ->assertJsonPath('data.id', $game->id)
        ->assertJsonPath('data.result.submitted_by.id', $player->id)
        ->assertJsonPath('data.result.submitted_at', $game->fresh()->submitted_at->toIso8601String());

    $scores = collect($response->json('data.attendees'))->keyBy('id');

    expect($scores[$mine->id]['scores']['victory-points'])->toEqual(85)
        ->and($scores[$theirs->id]['scores']['victory-points'])->toEqual(70);
});

test('the conflict body is shaped like the successful submission, so one reader handles both', function () {
    [$event, $game, $mine, $theirs] = submittedGame();

    $opponent = $theirs->memberships()->firstOrFail()->user;

    $conflict = $this->actingAs($opponent)
        ->postJson(route('events.games.result.store', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $mine->id => ['victory-points' => 10],
                $theirs->id => ['victory-points' => 99],
            ],
        ])
        ->assertStatus(409)
        ->json('data');

    $success = $this->getJson(route('events.games.show', ['event' => $event->slug, 'game' => $game->id]))
        ->assertSuccessful()
        ->json('data');

    expect(array_keys($conflict))->toBe(array_keys($success))
        ->and(array_keys($conflict['result']))->toBe(array_keys($success['result']));
});

test('a rejected submission still writes nothing', function () {
    [$event, $game, $mine, $theirs, $player] = submittedGame();

    $opponent = $theirs->memberships()->firstOrFail()->user;

    $this->actingAs($opponent)
        ->postJson(route('events.games.result.store', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $mine->id => ['victory-points' => 10],
                $theirs->id => ['victory-points' => 99],
            ],
        ])
        ->assertStatus(409);

    $game->refresh();

    expect($game->submitted_by_user_id)->toBe($player->id)
        ->and(GameScore::query()->where('game_id', $game->id)->where('event_attendee_id', $mine->id)->value('value'))->toBe('85.00')
        ->and(GameScore::query()->where('game_id', $game->id)->where('event_attendee_id', $theirs->id)->value('value'))->toBe('70.00');
});
