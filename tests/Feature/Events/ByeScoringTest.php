<?php

use App\Enums\EventOrganiserRole;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventScoreType;
use App\Models\Game;
use App\Models\GameScore;
use App\Models\Round;
use App\Models\User;

/**
 * @return array{0: Event, 1: Game, 2: EventAttendee, 3: User, 4: EventScoreType, 5: EventScoreType}
 */
function byeGame(): array
{
    $event = Event::factory()->active()->standingsVisible()->create();
    $matchPoints = EventScoreType::factory()->matchPoints(win: 3, draw: 1, loss: 0)->rankedAt(1)->for($event)->create(['display_order' => 0]);
    $victoryPoints = EventScoreType::factory()->victoryPoints()->rankedAt(2)->for($event)->create(['display_order' => 1]);

    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    $attendee = EventAttendee::factory()->for($event)->withMember()->create();

    $round = Round::factory()->for($event)->live()->create();
    $game = Game::factory()->for($round)->bye()->create();
    $game->attendees()->attach($attendee);

    return [$event, $game, $attendee, $organiser, $matchPoints, $victoryPoints];
}

test('an organiser enters victory points for a bye and it counts as a win', function () {
    [$event, $game, $attendee, $organiser, $matchPoints, $victoryPoints] = byeGame();

    $this->actingAs($organiser)
        ->putJson(route('events.games.result.update', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $attendee->id => ['victory-points' => 60],
            ],
        ])
        ->assertSuccessful();

    $scores = GameScore::query()->where('game_id', $game->id)->get()->keyBy('event_score_type_id');

    expect($scores)->toHaveCount(2)
        ->and($scores[$victoryPoints->id]->value)->toBe('60.00')
        ->and($scores[$matchPoints->id]->value)->toBe('3.00');
});

test('a bye is awarded its win as soon as the round is paired', function () {
    $event = pairableEvent();

    EventAttendee::factory()->count(3)->for($event)->withMember()->create();

    $round = generatePairings($event);

    $bye = $round->games()->where('is_bye', true)->sole();
    $matchPoints = $event->scoreTypes()->where('slug', 'match-points')->sole();

    $awarded = GameScore::query()
        ->where('game_id', $bye->id)
        ->where('event_score_type_id', $matchPoints->id)
        ->value('value');

    expect($awarded)->toBe('3.00');
});

test('a player cannot submit a result for a bye', function () {
    [$event, $game, $attendee] = byeGame();

    $player = $attendee->memberships()->first()->user;

    $this->actingAs($player)
        ->postJson(route('events.games.result.store', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [$attendee->id => ['victory-points' => 60]],
        ])
        ->assertForbidden();
});

test('a bye appears in standings with its awarded points', function () {
    [$event, $game, $attendee, $organiser, $matchPoints, $victoryPoints] = byeGame();

    $other = EventAttendee::factory()->for($event)->withMember()->create();

    $this->actingAs($organiser)
        ->putJson(route('events.games.result.update', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [$attendee->id => ['victory-points' => 60]],
        ])
        ->assertSuccessful();

    $response = $this->getJson(route('events.standings.index', ['event' => $event->slug]))
        ->assertSuccessful();

    $row = collect($response->json('data'))->firstWhere('attendee.id', $attendee->id);
    $scores = collect($row['scores'])->keyBy('score_type.slug');

    expect($row['position'])->toBe(1)
        ->and($scores['match-points']['value'])->toBe('3.00')
        ->and($scores['victory-points']['value'])->toBe('60.00')
        ->and(collect($response->json('data'))->firstWhere('attendee.id', $other->id)['position'])->toBe(2);
});

test('a bye is not an opponent', function () {
    [$event, $byeGameModel, $attendee] = byeGame();

    $opponent = EventAttendee::factory()->for($event)->withMember()->create();

    $round = Round::factory()->for($event)->live()->create(['number' => 2]);
    $played = Game::factory()->for($round)->create();
    $played->attendees()->attach([$attendee->id, $opponent->id]);

    expect($attendee->opponents()->pluck('event_attendees.id')->all())->toEqual([$opponent->id])
        ->and($byeGameModel->is_bye)->toBeTrue();
});

test('a bye win is awarded once, and entering victory points does not rewrite it', function () {
    [$event, $game, $attendee, $organiser, $matchPoints] = byeGame();

    // An Organiser has already adjusted the Bye's Match Points by hand.
    GameScore::query()->create([
        'game_id' => $game->id,
        'event_attendee_id' => $attendee->id,
        'event_score_type_id' => $matchPoints->id,
        'value' => 1,
    ]);

    $this->actingAs($organiser)
        ->putJson(route('events.games.result.update', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $attendee->id => ['victory-points' => 60],
            ],
        ])
        ->assertSuccessful();

    $matchPointsValue = GameScore::query()
        ->where('game_id', $game->id)
        ->where('event_score_type_id', $matchPoints->id)
        ->value('value');

    expect($matchPointsValue)->toBe('1.00');
});

test('result attribution cannot be mass assigned onto a game', function () {
    [, $game, , $organiser] = byeGame();

    $game->fill([
        'submitted_by_user_id' => $organiser->id,
        'submitted_at' => now(),
        'edited_by_user_id' => $organiser->id,
        'edited_at' => now(),
    ])->save();

    $game->refresh();

    expect($game->submitted_by_user_id)->toBeNull()
        ->and($game->submitted_at)->toBeNull()
        ->and($game->edited_by_user_id)->toBeNull()
        ->and($game->edited_at)->toBeNull();
});
