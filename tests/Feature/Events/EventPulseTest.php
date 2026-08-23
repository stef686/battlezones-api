<?php

use App\Enums\PollType;
use App\Models\Event;
use App\Models\EventPoll;
use App\Models\Game;
use App\Models\Round;
use Illuminate\Support\Facades\DB;

test('the pulse reports the current Round and a stamp for each live resource', function () {
    $event = pairableEvent();
    $round = Round::factory()->for($event)->live()->create(['number' => 1]);

    $this->getJson(route('events.pulse', $event))
        ->assertSuccessful()
        ->assertJsonPath('data.current_round.id', $round->id)
        ->assertJsonPath('data.current_round.number', 1)
        ->assertJsonStructure(['data' => ['current_round', 'rounds', 'standings', 'polls']]);
});

test('the current Round is null before anything is published', function () {
    $event = pairableEvent();
    Round::factory()->for($event)->create(['number' => 1, 'status' => 'draft']);

    $this->getJson(route('events.pulse', $event))
        ->assertSuccessful()
        ->assertJsonPath('data.current_round', null);
});

test('publishing a Round moves the rounds stamp and the current Round', function () {
    $event = pairableEvent();
    $round = Round::factory()->for($event)->create(['number' => 1, 'status' => 'draft']);

    $before = $this->getJson(route('events.pulse', $event))->json('data');

    $this->travel(1)->second();
    $round->forceFill(['status' => 'live'])->save();

    $after = $this->getJson(route('events.pulse', $event))->json('data');

    expect($after['rounds'])->not->toBe($before['rounds'])
        ->and($after['current_round']['id'])->toBe($round->id)
        ->and($before['current_round'])->toBeNull();
});

test('submitting a result moves the standings stamp', function () {
    $event = pairableEvent();
    Round::factory()->for($event)->live()->create(['number' => 1]);

    $before = $this->getJson(route('events.pulse', $event))->json('data.standings');

    $this->travel(1)->second();
    [$scored] = submittedGame();

    $after = $this->getJson(route('events.pulse', $scored))->json('data.standings');

    expect($after)->not->toBe($before);
});

test('correcting a result moves the standings stamp', function () {
    [$event, $game, $mine, $theirs] = submittedGame();

    $before = $this->getJson(route('events.pulse', $event))->json('data.standings');

    $this->travel(1)->second();

    $this->actingAs(organiserOf($event))
        ->putJson(route('events.games.result.update', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $mine->id => ['victory-points' => 50],
                $theirs->id => ['victory-points' => 90],
            ],
        ])
        ->assertSuccessful();

    expect($this->getJson(route('events.pulse', $event))->json('data.standings'))->not->toBe($before);
});

test('opening and closing a Poll moves the polls stamp', function () {
    $event = pairableEvent();
    $poll = EventPoll::factory()->for($event)->create(['type' => PollType::Painting]);

    $before = $this->getJson(route('events.pulse', $event))->json('data.polls');

    $this->travel(1)->second();
    $poll->forceFill(['opens_at' => now()->subMinute(), 'closes_at' => now()->addHour()])->save();

    $opened = $this->getJson(route('events.pulse', $event))->json('data.polls');
    expect($opened)->not->toBe($before);

    $this->travel(1)->second();
    $poll->forceFill(['closes_at' => now()->subSecond()])->save();

    expect($this->getJson(route('events.pulse', $event))->json('data.polls'))->not->toBe($opened);
});

test('the pulse stays cheap: a handful of aggregates, however big the Event', function () {
    $event = pairableEvent();
    Round::factory()->for($event)->live()->create(['number' => 1]);

    // Warm the route so the query count is the endpoint's own work.
    $this->getJson(route('events.pulse', $event))->assertSuccessful();

    $queries = [];
    DB::listen(function ($query) use (&$queries): void {
        // The throttle middleware reads the cache, which is a table here and
        // would not be on another driver. It is not this endpoint's work.
        if (! str_contains($query->sql, '`cache`')) {
            $queries[] = $query->sql;
        }
    });

    $this->getJson(route('events.pulse', $event))->assertSuccessful();

    // Binding the Event, finding the current Round, then one aggregate per
    // stamp. Nothing else, and nothing that reads a row to throw it away.
    expect($queries)->toHaveCount(5);

    foreach ($queries as $sql) {
        expect($sql)->not->toContain('select * from `game_scores`')
            ->and($sql)->not->toContain('select * from `games`');
    }

});

test('the pulse does not grow a query as the Event does', function () {
    $event = pairableEvent();
    $round = Round::factory()->for($event)->live()->create(['number' => 1]);

    $this->getJson(route('events.pulse', $event))->assertSuccessful();

    $queries = [];
    DB::listen(function ($query) use (&$queries): void {
        if (! str_contains($query->sql, '`cache`')) {
            $queries[] = true;
        }
    });

    $this->getJson(route('events.pulse', $event))->assertSuccessful();
    $withOneGame = count($queries);

    Game::factory()->count(8)->for($round)->create();

    $queries = [];
    $this->getJson(route('events.pulse', $event))->assertSuccessful();

    expect(count($queries))->toBe($withOneGame);
});

test('the pulse is public, since Standings and Rounds are', function () {
    $event = pairableEvent();

    $this->getJson(route('events.pulse', $event))->assertSuccessful();
});

test('the pulse 404s for an Event nobody may see', function () {
    $event = Event::factory()->draft()->create();

    $this->getJson(route('events.pulse', $event))->assertNotFound();
});
