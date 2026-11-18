<?php

use App\Actions\Events\StoreGameScores;
use App\Enums\RoundStatus;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventScheduleBlock;
use App\Models\Game;
use App\Models\GameScore;
use App\Models\Round;

/**
 * @return array{0: Event, 1: Round}
 */
function draftRoundOf(int $attendees = 4): array
{
    $event = pairableEvent();
    EventAttendee::factory()->count($attendees)->for($event)->withMember()->create();

    return [$event, generatePairings($event)];
}

function regenerateRoute(Event $event, Round $round): string
{
    return route('events.rounds.regenerate', ['event' => $event->slug, 'round' => $round->id]);
}

test('an organiser pairs the latest draft round again, keeping the round', function () {
    [$event, $round] = draftRoundOf();
    $oldGameIds = $round->games()->pluck('id')->all();

    $this->actingAs(organiserOf($event))
        ->postJson(regenerateRoute($event, $round))
        ->assertSuccessful()
        ->assertJsonPath('data.id', $round->id)
        ->assertJsonPath('data.number', 1)
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.games.0.table_number', 1)
        ->assertJsonPath('data.games.1.table_number', 2);

    expect($event->rounds()->count())->toBe(1)
        ->and($round->games()->count())->toBe(2)
        ->and($round->games()->whereIn('id', $oldGameIds)->exists())->toBeFalse();
});

test('the field is paired as it stands: dropped attendees out, late arrivals in', function () {
    [$event, $round] = draftRoundOf();
    $dropped = $event->attendees()->firstOrFail();
    $dropped->forceFill(['dropped_at' => now()])->save();
    $lateArrival = EventAttendee::factory()->for($event)->withMember()->create();

    $this->actingAs(organiserOf($event))
        ->postJson(regenerateRoute($event, $round))
        ->assertSuccessful();

    $paired = $round->games()->with('attendees')->get()->flatMap->attendees->pluck('id');

    expect($paired)->toContain($lateArrival->id)
        ->not->toContain($dropped->id)
        ->toHaveCount(4);
});

test('the old pairing is not counted as previous opponents', function () {
    [$event, $round] = draftRoundOf();
    $before = pairingsOf($round);

    $this->actingAs(organiserOf($event))
        ->postJson(regenerateRoute($event, $round))
        ->assertSuccessful()
        ->assertJsonPath('data.games.0.is_rematch', false)
        ->assertJsonPath('data.games.1.is_rematch', false);

    expect(pairingsOf($round))->toBe($before);
});

test('the old bye is not counted as already given, nor its match points kept', function () {
    [$event, $round] = draftRoundOf(attendees: 3);
    $byeHolder = $round->games()->where('is_bye', true)->sole()->attendees()->sole();

    $this->actingAs(organiserOf($event))
        ->postJson(regenerateRoute($event, $round))
        ->assertSuccessful();

    $bye = $round->games()->where('is_bye', true)->sole();

    expect($bye->attendees()->sole()->id)->toBe($byeHolder->id)
        ->and(GameScore::query()->where('event_attendee_id', $byeHolder->id)->count())->toBe(1);
});

test('the bye can move, taking its match points with it', function () {
    [$event, $round] = draftRoundOf(attendees: 3);
    $oldByeHolder = $round->games()->where('is_bye', true)->sole()->attendees()->sole();
    EventAttendee::factory()->count(2)->for($event)->withMember()->create();

    $this->actingAs(organiserOf($event))
        ->postJson(regenerateRoute($event, $round))
        ->assertSuccessful();

    $newByeHolder = $round->games()->where('is_bye', true)->sole()->attendees()->sole();
    $matchPoints = $event->scoreTypes()->where('slug', 'match-points')->sole();

    expect($newByeHolder->id)->not->toBe($oldByeHolder->id)
        ->and(GameScore::query()->where('event_attendee_id', $oldByeHolder->id)->exists())->toBeFalse()
        ->and(GameScore::query()
            ->where('event_attendee_id', $newByeHolder->id)
            ->where('event_score_type_id', $matchPoints->id)
            ->value('value'))->toEqual($matchPoints->win_points);
});

test('a schedule block linked to the round still points at it', function () {
    [$event, $round] = draftRoundOf();
    $block = EventScheduleBlock::factory()->for($event)->round($round->id)->create();

    $this->actingAs(organiserOf($event))
        ->postJson(regenerateRoute($event, $round))
        ->assertSuccessful();

    expect($block->fresh()?->round_id)->toBe($round->id);
});

test('a round with results cannot be paired again', function (int $attendees, bool $onTheBye) {
    [$event, $round] = draftRoundOf($attendees);
    $game = $round->games()->where('is_bye', $onTheBye)->firstOrFail();
    $oldGameIds = $round->games()->pluck('id')->all();

    GameScore::factory()->create([
        'game_id' => $game->id,
        'event_attendee_id' => $game->attendees()->firstOrFail()->id,
        'event_score_type_id' => $event->scoreTypes()->where('slug', 'victory-points')->sole()->id,
        'value' => 60,
    ]);

    $this->actingAs(organiserOf($event))
        ->postJson(regenerateRoute($event, $round))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Round 1 already has results, so it cannot be paired again.');

    expect($round->games()->pluck('id')->all())->toEqualCanonicalizing($oldGameIds);
})->with([
    'a score on a contested game' => [4, false],
    'victory points entered on a bye' => [3, true],
]);

test('a live round cannot be paired again', function () {
    [$event, $round] = draftRoundOf();
    $round->update(['status' => RoundStatus::Live]);

    $this->actingAs(organiserOf($event))
        ->postJson(regenerateRoute($event, $round))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Round 1 is live, so it cannot be paired again. Unpublish it first.');
});

test('only the latest round can be paired again', function () {
    $event = pairableEvent();
    $first = Round::factory()->for($event)->create(['number' => 1]);
    Round::factory()->for($event)->create(['number' => 2]);

    $this->actingAs(organiserOf($event))
        ->postJson(regenerateRoute($event, $first))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Round 1 is not the latest Round, so it cannot be paired again.');
});

test('a field that can no longer be paired leaves the old pairing exactly as it was', function () {
    [$event, $round] = draftRoundOf();
    $before = pairingsOf($round);
    $oldGameIds = $round->games()->pluck('id')->all();
    $event->attendees()->take(3)->get()->each(fn (EventAttendee $attendee) => $attendee->forceFill(['dropped_at' => now()])->save());

    $this->actingAs(organiserOf($event))
        ->postJson(regenerateRoute($event, $round))
        ->assertStatus(422)
        ->assertJsonPath('message', 'There are too few Attendees to pair a Round.');

    expect($round->games()->pluck('id')->all())->toEqualCanonicalizing($oldGameIds)
        ->and(pairingsOf($round))->toBe($before);
});

test('a player may not pair a round again', function () {
    [$event, $round] = draftRoundOf();
    $oldGameIds = $round->games()->pluck('id')->all();

    $this->actingAs(playerOf($event))
        ->postJson(regenerateRoute($event, $round))
        ->assertForbidden();

    expect($round->games()->pluck('id')->all())->toEqualCanonicalizing($oldGameIds);
});

test('a later round is paired again on the results of the round before', function () {
    [$event, $first] = draftRoundOf();
    $first->update(['status' => RoundStatus::Live]);
    $victoryPoints = $event->scoreTypes()->where('slug', 'victory-points')->sole();

    $first->games()->with('attendees')->get()->each(function (Game $game) use ($victoryPoints): void {
        [$home, $away] = $game->attendees->all();

        app(StoreGameScores::class)->execute($game, [
            $home->id => [$victoryPoints->id => 80],
            $away->id => [$victoryPoints->id => 40],
        ]);
    });

    $second = generatePairings($event);

    $this->actingAs(organiserOf($event))
        ->postJson(regenerateRoute($event, $second))
        ->assertSuccessful()
        ->assertJsonPath('data.id', $second->id)
        ->assertJsonPath('data.number', 2);
});
