<?php

use App\Enums\EventOrganiserRole;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventScoreType;
use App\Models\Game;
use App\Models\GameResultFlag;
use App\Models\Round;
use App\Models\User;

test('a player in the game can flag a submitted result with a reason', function () {
    [$event, $game, , , $player] = submittedGame();

    $this->actingAs($player)
        ->postJson(route('events.games.flag.store', ['event' => $event->slug, 'game' => $game->id]), [
            'reason' => 'We agreed 85-70 the other way round.',
        ])
        ->assertSuccessful();

    $flag = GameResultFlag::query()->where('game_id', $game->id)->sole();

    expect($flag->flagged_by_user_id)->toBe($player->id)
        ->and($flag->reason)->toBe('We agreed 85-70 the other way round.')
        ->and($flag->resolved_at)->toBeNull();
});

test('a player who is not in the game cannot flag it', function () {
    [$event, $game] = submittedGame();

    $outsider = User::factory()->create();
    EventAttendee::factory()->for($event)->withMember($outsider)->create();

    $this->actingAs($outsider)
        ->postJson(route('events.games.flag.store', ['event' => $event->slug, 'game' => $game->id]))
        ->assertForbidden();

    expect(GameResultFlag::query()->where('game_id', $game->id)->exists())->toBeFalse();
});

test('an organiser can flag a result', function () {
    [$event, $game] = submittedGame();

    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    $this->actingAs($organiser)
        ->postJson(route('events.games.flag.store', ['event' => $event->slug, 'game' => $game->id]))
        ->assertSuccessful();

    expect(GameResultFlag::query()->where('game_id', $game->id)->count())->toBe(1);
});

test('flagging again while a flag is open does not create a second record', function () {
    [$event, $game, $mine, $theirs, $player] = submittedGame();

    $opponent = $theirs->memberships()->first()->user;

    $this->actingAs($player)
        ->postJson(route('events.games.flag.store', ['event' => $event->slug, 'game' => $game->id]), ['reason' => 'First reason'])
        ->assertSuccessful();

    $this->actingAs($opponent)
        ->postJson(route('events.games.flag.store', ['event' => $event->slug, 'game' => $game->id]), ['reason' => 'Second reason'])
        ->assertSuccessful();

    $flag = GameResultFlag::query()->where('game_id', $game->id)->sole();

    expect($flag->flagged_by_user_id)->toBe($player->id)
        ->and($flag->reason)->toBe('First reason');
});

test('a game with no submitted result cannot be flagged', function () {
    $event = Event::factory()->active()->create();
    EventScoreType::factory()->victoryPoints()->for($event)->create();

    $player = User::factory()->create();
    $mine = EventAttendee::factory()->for($event)->withMember($player)->create();
    $theirs = EventAttendee::factory()->for($event)->withMember()->create();

    $round = Round::factory()->for($event)->live()->create();
    $game = Game::factory()->for($round)->create();
    $game->attendees()->attach([$mine->id, $theirs->id]);

    $this->actingAs($player)
        ->postJson(route('events.games.flag.store', ['event' => $event->slug, 'game' => $game->id]))
        ->assertStatus(422);
});

test('flagging requires authentication', function () {
    [$event, $game] = submittedGame();

    auth()->forgetGuards();

    $this->postJson(route('events.games.flag.store', ['event' => $event->slug, 'game' => $game->id]))
        ->assertUnauthorized();
});

test('organisers see flagged results', function () {
    [$event, $game, , , $player] = submittedGame();

    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    $this->actingAs($player)
        ->postJson(route('events.games.flag.store', ['event' => $event->slug, 'game' => $game->id]), ['reason' => 'Scores are reversed'])
        ->assertSuccessful();

    $response = $this->actingAs($organiser)
        ->getJson(route('events.flags.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');

    expect($response->json('data.0.game_id'))->toBe($game->id)
        ->and($response->json('data.0.reason'))->toBe('Scores are reversed')
        ->and($response->json('data.0.flagged_by.id'))->toBe($player->id)
        ->and($response->json('data.0.game.table_number'))->toBe($game->table_number)
        ->and($response->json('data.0.game.round.number'))->toBe($game->round->number);
});

test('players cannot read the flag queue', function () {
    [$event, $game, , , $player] = submittedGame();

    $this->actingAs($player)
        ->getJson(route('events.flags.index', ['event' => $event->slug]))
        ->assertForbidden();
});

test('an organiser resolves a flag without editing the result', function () {
    [$event, $game, , , $player] = submittedGame();

    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    $this->actingAs($player)
        ->postJson(route('events.games.flag.store', ['event' => $event->slug, 'game' => $game->id]))
        ->assertSuccessful();

    $this->actingAs($organiser)
        ->postJson(route('events.games.flag.resolve', ['event' => $event->slug, 'game' => $game->id]))
        ->assertSuccessful();

    $flag = GameResultFlag::query()->where('game_id', $game->id)->sole();

    expect($flag->resolved_at)->not->toBeNull()
        ->and($flag->resolved_by_user_id)->toBe($organiser->id)
        ->and($game->refresh()->edited_at)->toBeNull();

    $this->actingAs($organiser)
        ->getJson(route('events.flags.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

test('a resolved flag can be raised again', function () {
    [$event, $game, , , $player] = submittedGame();

    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    $url = route('events.games.flag.store', ['event' => $event->slug, 'game' => $game->id]);

    $this->actingAs($player)->postJson($url)->assertSuccessful();
    $this->actingAs($organiser)
        ->postJson(route('events.games.flag.resolve', ['event' => $event->slug, 'game' => $game->id]))
        ->assertSuccessful();
    $this->actingAs($player)->postJson($url, ['reason' => 'Still wrong'])->assertSuccessful();

    expect(GameResultFlag::query()->where('game_id', $game->id)->count())->toBe(2)
        ->and(GameResultFlag::query()->where('game_id', $game->id)->unresolved()->sole()->reason)->toBe('Still wrong');
});

test('resolving is rejected when nothing is flagged', function () {
    [$event, $game] = submittedGame();

    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    $this->actingAs($organiser)
        ->postJson(route('events.games.flag.resolve', ['event' => $event->slug, 'game' => $game->id]))
        ->assertNotFound();
});

test('players cannot resolve a flag', function () {
    [$event, $game, , , $player] = submittedGame();

    $this->actingAs($player)
        ->postJson(route('events.games.flag.store', ['event' => $event->slug, 'game' => $game->id]))
        ->assertSuccessful();

    $this->actingAs($player)
        ->postJson(route('events.games.flag.resolve', ['event' => $event->slug, 'game' => $game->id]))
        ->assertForbidden();
});

test('correcting a flagged result moves the standings and empties the queue', function () {
    [$event, $game, $mine, $theirs, $player] = submittedGame();

    $event->forceFill(['settings' => $event->settings->with(['standings_visible' => true])])->save();

    $organiser = organiserOf($event);

    $this->actingAs($player)
        ->postJson(route('events.games.flag.store', ['event' => $event->slug, 'game' => $game->id]), [
            'reason' => 'It went in the wrong way round.',
        ])
        ->assertSuccessful();

    // The Organiser decides the score, then closes the dispute they decided.
    $this->actingAs($organiser)
        ->putJson(route('events.games.result.update', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $mine->id => ['victory-points' => 70],
                $theirs->id => ['victory-points' => 85],
            ],
        ])
        ->assertSuccessful();

    $this->actingAs($organiser)
        ->postJson(route('events.games.flag.resolve', ['event' => $event->slug, 'game' => $game->id]))
        ->assertSuccessful();

    $standings = collect($this->getJson(route('events.standings.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->json('data'));

    $winner = $standings->firstWhere('attendee.id', $theirs->id);

    expect(collect($winner['scores'])->keyBy('score_type.slug')['victory-points']['value'])->toBe('85.00')
        ->and($winner['position'])->toBe(1)
        ->and($game->refresh()->edited_by_user_id)->toBe($organiser->id);

    $this->actingAs($organiser)
        ->getJson(route('events.flags.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});
