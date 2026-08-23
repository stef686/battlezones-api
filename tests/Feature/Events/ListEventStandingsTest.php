<?php

use App\Actions\Events\StoreGameScores;
use App\Models\Club;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventScoreType;
use App\Models\Faction;
use App\Models\Game;
use App\Models\Round;
use App\Models\User;

/**
 * Play a Game between two Attendees, scoring it through the same action a
 * Player submission uses so Standings are read from real results.
 */
function playGame(Round $round, EventAttendee $home, EventAttendee $away, float $homeScore, float $awayScore, EventScoreType $scoreType): Game
{
    $game = Game::factory()->for($round)->create();
    $game->attendees()->attach([$home->id, $away->id]);

    app(StoreGameScores::class)->execute($game, [
        $home->id => [$scoreType->id => $homeScore],
        $away->id => [$scoreType->id => $awayScore],
    ]);

    return $game;
}

test('it ranks attendees on match points before victory points', function () {
    $event = Event::factory()->published()->standingsVisible()->create();
    $matchPoints = EventScoreType::factory()->matchPoints(win: 3, draw: 1, loss: 0)->rankedAt(1)->for($event)->create(['display_order' => 0]);
    $victoryPoints = EventScoreType::factory()->victoryPoints()->rankedAt(2)->for($event)->create(['display_order' => 1]);

    $winner = EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'Winner']))->create();
    $loser = EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'Loser']))->create();

    $round = Round::factory()->for($event)->live()->create();
    playGame($round, $winner, $loser, 85, 70, $victoryPoints);

    $response = $this->getJson(route('events.standings.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonStructure(['data', 'links', 'meta'])
        ->assertJsonCount(2, 'data');

    expect(collect($response->json('data'))->pluck('attendee.name')->all())->toEqual(['Winner', 'Loser'])
        ->and(collect($response->json('data'))->pluck('position')->all())->toEqual([1, 2]);

    $scores = collect($response->json('data.0.scores'))->keyBy('score_type.slug');

    expect($scores['match-points']['value'])->toBe('3.00')
        ->and($scores['victory-points']['value'])->toBe('85.00')
        ->and($matchPoints->slug)->toBe('match-points');
});

test('attendees on equal scores share a position', function () {
    $event = Event::factory()->published()->standingsVisible()->create();
    EventScoreType::factory()->matchPoints(win: 3, draw: 1, loss: 0)->rankedAt(1)->for($event)->create(['display_order' => 0]);
    $victoryPoints = EventScoreType::factory()->victoryPoints()->rankedAt(2)->for($event)->create(['display_order' => 1]);

    $drawOne = EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'Draw One']))->create();
    $drawTwo = EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'Draw Two']))->create();
    $trailing = EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'Trailing']))->create();
    $alsoTrailing = EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'Also Trailing']))->create();

    $round = Round::factory()->for($event)->live()->create();
    playGame($round, $drawOne, $drawTwo, 50, 50, $victoryPoints);
    playGame($round, $trailing, $alsoTrailing, 20, 20, $victoryPoints);

    $response = $this->getJson(route('events.standings.index', ['event' => $event->slug]))
        ->assertSuccessful();

    expect(collect($response->json('data'))->pluck('position')->all())->toEqual([1, 1, 3, 3]);
});

test('an attendee who has not played appears on zero', function () {
    $event = Event::factory()->published()->standingsVisible()->create();
    EventScoreType::factory()->matchPoints()->rankedAt(1)->for($event)->create(['display_order' => 0]);
    $victoryPoints = EventScoreType::factory()->victoryPoints()->rankedAt(2)->for($event)->create(['display_order' => 1]);

    $played = EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'Played']))->create();
    $opponent = EventAttendee::factory()->for($event)->withMember()->create();
    EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'Late Arrival']))->create();

    $round = Round::factory()->for($event)->live()->create();
    playGame($round, $played, $opponent, 85, 70, $victoryPoints);

    $response = $this->getJson(route('events.standings.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');

    $lateArrival = collect($response->json('data'))->firstWhere('attendee.name', 'Late Arrival');
    $scores = collect($lateArrival['scores'])->keyBy('score_type.slug');

    expect($lateArrival['position'])->toBe(3)
        ->and($scores['match-points']['value'])->toBe('0.00')
        ->and($scores['victory-points']['value'])->toBe('0.00');
});

test('standings change as soon as a result is submitted', function () {
    $event = Event::factory()->active()->standingsVisible()->create();
    EventScoreType::factory()->matchPoints(win: 3, draw: 1, loss: 0)->rankedAt(1)->for($event)->create(['display_order' => 0]);
    $victoryPoints = EventScoreType::factory()->victoryPoints()->rankedAt(2)->for($event)->create(['display_order' => 1]);

    $player = User::factory()->create();
    $mine = EventAttendee::factory()->for($event)->withMember($player)->create();
    $theirs = EventAttendee::factory()->for($event)->withMember()->create();

    $round = Round::factory()->for($event)->live()->create();
    $game = Game::factory()->for($round)->create();
    $game->attendees()->attach([$mine->id, $theirs->id]);

    $this->getJson(route('events.standings.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonPath('data.0.scores.0.value', '0.00');

    $this->actingAs($player)
        ->postJson(route('events.games.result.store', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $mine->id => ['victory-points' => 85],
                $theirs->id => ['victory-points' => 70],
            ],
        ])
        ->assertSuccessful();

    $response = $this->getJson(route('events.standings.index', ['event' => $event->slug]))
        ->assertSuccessful();

    expect($response->json('data.0.attendee.id'))->toBe($mine->id)
        ->and($response->json('data.0.position'))->toBe(1)
        ->and(collect($response->json('data.0.scores'))->keyBy('score_type.slug')['match-points']['value'])->toBe('3.00');
});

test('sorting by a score type reorders the list but keeps the true position', function () {
    $event = Event::factory()->published()->standingsVisible()->create();
    EventScoreType::factory()->matchPoints(win: 3, draw: 1, loss: 0)->rankedAt(1)->for($event)->create(['display_order' => 0]);
    $victoryPoints = EventScoreType::factory()->victoryPoints()->rankedAt(2)->for($event)->create(['display_order' => 1]);

    $grinder = EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'Two Narrow Wins']))->create();
    $blowout = EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'One Blowout']))->create();
    $filler = EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'Filler']))->create();
    $otherFiller = EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'Other Filler']))->create();

    $roundOne = Round::factory()->for($event)->live()->create(['number' => 1]);
    playGame($roundOne, $grinder, $filler, 51, 50, $victoryPoints);
    playGame($roundOne, $blowout, $otherFiller, 100, 0, $victoryPoints);

    $roundTwo = Round::factory()->for($event)->live()->create(['number' => 2]);
    playGame($roundTwo, $grinder, $otherFiller, 51, 50, $victoryPoints);

    $response = $this->getJson(route('events.standings.index', ['event' => $event->slug, 'sort_by' => 'victory-points']))
        ->assertSuccessful();

    $rows = collect($response->json('data'));

    expect($rows->pluck('attendee.name')->first())->toBe('Two Narrow Wins')
        ->and($rows->firstWhere('attendee.name', 'One Blowout')['position'])->toBe(2)
        ->and($rows->firstWhere('attendee.name', 'Two Narrow Wins')['position'])->toBe(1);
});

test('it sorts ascending for a score type sorted ascending', function () {
    $event = Event::factory()->published()->standingsVisible()->create();
    $penalties = EventScoreType::factory()->for($event)->create([
        'name' => 'Penalties',
        'slug' => 'penalties',
        'sort_direction' => 'asc',
        'display_order' => 0,
    ]);

    $few = EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'Few Penalties']))->create();
    $many = EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'Many Penalties']))->create();

    $round = Round::factory()->for($event)->live()->create();
    playGame($round, $few, $many, 5, 50, $penalties);

    $response = $this->getJson(route('events.standings.index', ['event' => $event->slug, 'sort_by' => 'penalties']))
        ->assertSuccessful();

    expect(collect($response->json('data'))->pluck('attendee.name')->all())->toEqual(['Few Penalties', 'Many Penalties']);
});

test('it returns 422 for an unknown sort_by value', function () {
    $event = Event::factory()->published()->standingsVisible()->create();
    EventScoreType::factory()->victoryPoints()->for($event)->create();

    $this->getJson(route('events.standings.index', ['event' => $event->slug, 'sort_by' => 'nonexistent-type']))
        ->assertStatus(422);
});

test('it returns 404 when standings_visible is false', function () {
    $event = Event::factory()->published()->create();

    $this->getJson(route('events.standings.index', ['event' => $event->slug]))
        ->assertNotFound();
});

test('it returns 404 for non-publicly-visible events', function (string $state) {
    $event = Event::factory()->{$state}()->standingsVisible()->create();

    $this->getJson(route('events.standings.index', ['event' => $event->slug]))
        ->assertNotFound();
})->with(['draft', 'cancelled']);

test('it searches by attendee user name', function () {
    $event = Event::factory()->published()->standingsVisible()->create();
    $victoryPoints = EventScoreType::factory()->victoryPoints()->rankedAt(1)->for($event)->create();

    $match = EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'Alice Anderson']))->create();
    $miss = EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'Bob Brown']))->create();

    $round = Round::factory()->for($event)->live()->create();
    playGame($round, $match, $miss, 85, 70, $victoryPoints);

    $this->getJson(route('events.standings.index', ['event' => $event->slug, 'search' => 'alice']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.attendee.name', 'Alice Anderson')
        ->assertJsonPath('data.0.position', 1);
});

test('it searches by faction name', function () {
    $event = Event::factory()->published()->standingsVisible()->create();

    $matchFaction = Faction::factory()->create(['name' => 'Space Marines']);
    $missFaction = Faction::factory()->create(['name' => 'Tyranids']);

    EventAttendee::factory()->for($event)->withMember(null, ['faction_id' => $matchFaction->id])->create();
    EventAttendee::factory()->for($event)->withMember(null, ['faction_id' => $missFaction->id])->create();

    $this->getJson(route('events.standings.index', ['event' => $event->slug, 'search' => 'marines']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('it searches by club name', function () {
    $event = Event::factory()->published()->standingsVisible()->create();

    $club = Club::factory()->create(['name' => 'London Warlords']);
    $user = User::factory()->create();
    $user->clubs()->attach($club);

    EventAttendee::factory()->for($event)->withMember($user)->create();
    EventAttendee::factory()->for($event)->withMember()->create();

    $this->getJson(route('events.standings.index', ['event' => $event->slug, 'search' => 'warlords']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('it includes score type metadata', function () {
    $event = Event::factory()->published()->standingsVisible()->create();
    $victoryPoints = EventScoreType::factory()->for($event)->create([
        'name' => 'Battle Points',
        'slug' => 'battle-points',
        'sort_direction' => 'desc',
        'ranking_order' => 1,
        'display_order' => 0,
    ]);

    $attendee = EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'Alice']))->create();
    $opponent = EventAttendee::factory()->for($event)->withMember()->create();

    $round = Round::factory()->for($event)->live()->create();
    playGame($round, $attendee, $opponent, 75.5, 20, $victoryPoints);

    $data = $this->getJson(route('events.standings.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->json('data.0');

    expect($data['position'])->toBe(1)
        ->and($data['attendee']['name'])->toBe('Alice')
        ->and($data['scores'][0]['value'])->toBe('75.50')
        ->and($data['scores'][0]['score_type']['name'])->toBe('Battle Points')
        ->and($data['scores'][0]['score_type']['slug'])->toBe('battle-points')
        ->and($data['scores'][0]['score_type']['sort_direction'])->toBe('desc');
});
