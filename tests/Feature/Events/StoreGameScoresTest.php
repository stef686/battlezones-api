<?php

use App\Actions\Events\StoreGameScores;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventScoreType;
use App\Models\Game;
use App\Models\GameScore;
use App\Models\Round;

test('it stores scores for submitted score types', function () {
    $event = Event::factory()->active()->create();
    $vp = EventScoreType::factory()->victoryPoints()->for($event)->create();

    $round = Round::factory()->for($event)->create();
    $game = Game::factory()->for($round)->create();

    $attendee1 = EventAttendee::factory()->for($event)->withMember()->create();
    $attendee2 = EventAttendee::factory()->for($event)->withMember()->create();
    $game->attendees()->attach([$attendee1->id, $attendee2->id]);

    app(StoreGameScores::class)->execute($game, [
        $attendee1->id => [$vp->id => 85],
        $attendee2->id => [$vp->id => 70],
    ]);

    expect(GameScore::query()->where('game_id', $game->id)->count())->toBe(2)
        ->and(GameScore::query()->where('event_attendee_id', $attendee1->id)->where('event_score_type_id', $vp->id)->value('value'))->toBe('85.00');
});

test('it rejects client-supplied values for derived score types', function () {
    $event = Event::factory()->active()->create();
    $vp = EventScoreType::factory()->victoryPoints()->for($event)->create();
    $mp = EventScoreType::factory()->matchPoints()->rankedAt(1)->for($event)->create();

    $round = Round::factory()->for($event)->create();
    $game = Game::factory()->for($round)->create();

    $attendee1 = EventAttendee::factory()->for($event)->withMember()->create();
    $attendee2 = EventAttendee::factory()->for($event)->withMember()->create();
    $game->attendees()->attach([$attendee1->id, $attendee2->id]);

    app(StoreGameScores::class)->execute($game, [
        $attendee1->id => [$vp->id => 85, $mp->id => 3],
        $attendee2->id => [$vp->id => 70, $mp->id => 0],
    ]);
})->throws(InvalidArgumentException::class, 'Cannot supply values for derived score types');

test('it computes match points from victory points', function () {
    $event = Event::factory()->active()->create();
    $vp = EventScoreType::factory()->victoryPoints()->for($event)->create();
    $mp = EventScoreType::factory()->matchPoints(win: 3, draw: 1, loss: 0)->rankedAt(1)->for($event)->create();

    $round = Round::factory()->for($event)->create();
    $game = Game::factory()->for($round)->create();

    $attendee1 = EventAttendee::factory()->for($event)->withMember()->create();
    $attendee2 = EventAttendee::factory()->for($event)->withMember()->create();
    $game->attendees()->attach([$attendee1->id, $attendee2->id]);

    app(StoreGameScores::class)->execute($game, [
        $attendee1->id => [$vp->id => 85],
        $attendee2->id => [$vp->id => 70],
    ]);

    expect(GameScore::query()->where('game_id', $game->id)->count())->toBe(4);

    $mp1 = GameScore::query()->where('game_id', $game->id)->where('event_attendee_id', $attendee1->id)->where('event_score_type_id', $mp->id)->value('value');
    $mp2 = GameScore::query()->where('game_id', $game->id)->where('event_attendee_id', $attendee2->id)->where('event_score_type_id', $mp->id)->value('value');

    expect($mp1)->toBe('3.00')
        ->and($mp2)->toBe('0.00');
});

test('it computes draw match points when victory points are equal', function () {
    $event = Event::factory()->active()->create();
    $vp = EventScoreType::factory()->victoryPoints()->for($event)->create();
    $mp = EventScoreType::factory()->matchPoints(win: 3, draw: 1, loss: 0)->rankedAt(1)->for($event)->create();

    $round = Round::factory()->for($event)->create();
    $game = Game::factory()->for($round)->create();

    $attendee1 = EventAttendee::factory()->for($event)->withMember()->create();
    $attendee2 = EventAttendee::factory()->for($event)->withMember()->create();
    $game->attendees()->attach([$attendee1->id, $attendee2->id]);

    app(StoreGameScores::class)->execute($game, [
        $attendee1->id => [$vp->id => 50],
        $attendee2->id => [$vp->id => 50],
    ]);

    $mp1 = GameScore::query()->where('game_id', $game->id)->where('event_attendee_id', $attendee1->id)->where('event_score_type_id', $mp->id)->value('value');
    $mp2 = GameScore::query()->where('game_id', $game->id)->where('event_attendee_id', $attendee2->id)->where('event_score_type_id', $mp->id)->value('value');

    expect($mp1)->toBe('1.00')
        ->and($mp2)->toBe('1.00');
});
