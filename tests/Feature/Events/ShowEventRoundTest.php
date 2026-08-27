<?php

use App\Enums\Allegiance;
use App\Enums\EventOrganiserRole;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventScoreType;
use App\Models\Faction;
use App\Models\Game;
use App\Models\GameScore;
use App\Models\Round;
use App\Models\User;

test('it returns round detail with games ordered by table number', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create(['number' => 1, 'name' => 'Round One']);

    $vp = EventScoreType::factory()->victoryPoints()->for($event)->create();

    $faction = Faction::factory()->create(['name' => 'Space Marines']);
    $user1 = User::factory()->create(['name' => 'Alice']);
    $user2 = User::factory()->create(['name' => 'Bob']);

    $attendee1 = EventAttendee::factory()->for($event)->withMember($user1, ['faction_id' => $faction->id])->create();
    $attendee2 = EventAttendee::factory()->for($event)->withMember($user2)->create();

    $game2 = Game::factory()->for($round)->create(['table_number' => 2]);
    $game1 = Game::factory()->for($round)->create(['table_number' => 1]);

    $game1->attendees()->attach($attendee1);
    $game1->attendees()->attach($attendee2);
    $game2->attendees()->attach($attendee1);

    GameScore::factory()->create(['game_id' => $game1->id, 'event_attendee_id' => $attendee1->id, 'event_score_type_id' => $vp->id, 'value' => 85]);
    GameScore::factory()->create(['game_id' => $game1->id, 'event_attendee_id' => $attendee2->id, 'event_score_type_id' => $vp->id, 'value' => 70]);

    $response = $this->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertSuccessful();

    expect($response->json('data.id'))->toBe($round->id)
        ->and($response->json('data.number'))->toBe(1)
        ->and($response->json('data.name'))->toBe('Round One')
        ->and($response->json('data.games'))->toHaveCount(2);

    $firstGame = $response->json('data.games.0');
    expect($firstGame['table_number'])->toBe(1)
        ->and($firstGame['is_bye'])->toBeFalse()
        ->and($firstGame['attendees'])->toHaveCount(2)
        ->and($firstGame['attendees'][0]['name'])->toBe('Alice')
        ->and($firstGame['attendees'][0]['members'][0]['name'])->toBe('Alice')
        ->and($firstGame['attendees'][0]['members'][0]['faction']['name'])->toBe('Space Marines')
        ->and($firstGame['attendees'][0]['scores'])->toBe(['victory-points' => '85.00']);
});

test('it returns 404 if round does not belong to event', function () {
    $event = Event::factory()->active()->create();
    $otherEvent = Event::factory()->active()->create();
    $round = Round::factory()->for($otherEvent)->create();

    $this->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertNotFound();
});

test('it returns 404 for non-publicly-visible events', function (string $state) {
    $event = Event::factory()->{$state}()->create();
    $round = Round::factory()->for($event)->create();

    $this->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertNotFound();
})->with(['draft', 'cancelled']);

test('it returns 404 for published events (no rounds visible)', function () {
    $event = Event::factory()->published()->create();
    $round = Round::factory()->for($event)->create();

    $this->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertNotFound();
});

test('it returns 404 for a draft round to players', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->create(['number' => 1]);

    $this->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertNotFound();
});

test('it returns a draft round to organisers', function () {
    $event = Event::factory()->active()->create();
    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);
    $round = Round::factory()->for($event)->create(['number' => 1]);

    $this->actingAs($organiser)
        ->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'draft');
});

test('a round tells an organiser which tables are still playing', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create();

    $done = Game::factory()->for($round)->create(['table_number' => 1, 'submitted_at' => now()]);
    $outstanding = Game::factory()->for($round)->create(['table_number' => 2, 'submitted_at' => null]);

    $response = $this->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertOk();

    $games = collect($response->json('data.games'))->keyBy('id');

    expect($games[$done->id]['result']['submitted_at'])->not->toBeNull()
        ->and($games[$outstanding->id]['result']['submitted_at'])->toBeNull()
        ->and($games[$done->id]['result']['is_flagged'])->toBeFalse();
});

test('a round carries each attendee\'s allegiance, so a pairing can be seen to be opposed', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create();
    $game = Game::factory()->for($round)->create(['table_number' => 1]);

    $loyalist = EventAttendee::factory()->for($event)->create(['allegiance' => Allegiance::Loyalist]);
    $traitor = EventAttendee::factory()->for($event)->create(['allegiance' => Allegiance::Traitor]);
    $game->attendees()->sync([$loyalist->id, $traitor->id]);

    $response = $this->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertOk();

    expect(collect($response->json('data.games.0.attendees'))->pluck('allegiance')->sort()->values()->all())
        ->toBe(['loyalist', 'traitor']);
});

test('a round lists each game\'s attendees in the order they were paired', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create();
    $game = Game::factory()->for($round)->create(['table_number' => 1]);

    $second = EventAttendee::factory()->for($event)->create(['allegiance' => Allegiance::Traitor]);
    $first = EventAttendee::factory()->for($event)->create(['allegiance' => Allegiance::Loyalist]);

    // Attached second-then-first, so insertion order and id order disagree.
    $game->attendees()->attach($second->id);
    $game->attendees()->attach($first->id);

    // The client previews a swap by exchanging the second Attendee, so the
    // order it is shown has to be the order the swap acts on.
    $response = $this->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertOk();

    expect(collect($response->json('data.games.0.attendees'))->pluck('id')->all())
        ->toBe([$second->id, $first->id]);
});

test('it names the score columns a round is played on, whether or not results are in', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create();

    // Declared out of display order, to prove the payload sorts them.
    EventScoreType::factory()->victoryPoints()->for($event)->create(['display_order' => 2]);
    EventScoreType::factory()->matchPoints()->for($event)->create(['display_order' => 1]);

    $game = Game::factory()->for($round)->create(['table_number' => 1]);
    $game->attendees()->attach(EventAttendee::factory()->for($event)->withMember()->create());

    $response = $this->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertSuccessful();

    expect(array_column($response->json('data.score_types'), 'slug'))
        ->toBe(['match-points', 'victory-points'])
        // Nothing has been scored, and the columns are sent all the same: the
        // screen shows a Game waiting on two numbers, not a Game with none.
        ->and($response->json('data.games.0.attendees.0.scores'))->toBe([]);
});

test('it tells an organiser a game repeats a pairing, and tells a player nothing', function () {
    $event = Event::factory()->active()->create();
    $first = Round::factory()->for($event)->live()->create(['number' => 1]);
    $second = Round::factory()->for($event)->live()->create(['number' => 2]);

    $home = EventAttendee::factory()->for($event)->withMember()->create();
    $away = EventAttendee::factory()->for($event)->withMember()->create();

    foreach ([$first, $second] as $round) {
        $game = Game::factory()->for($round)->create(['table_number' => 1]);
        $game->attendees()->attach([$home->id, $away->id]);
    }

    $url = route('events.rounds.show', ['event' => $event->slug, 'round' => $second->id]);

    // Not sent the key at all, rather than sent a false: how the field was
    // paired is not a Player's to read.
    expect($this->getJson($url)->assertSuccessful()->json('data.games.0'))
        ->not->toHaveKey('is_rematch');

    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    expect($this->actingAs($organiser)->getJson($url)->assertSuccessful()->json('data.games.0.is_rematch'))
        ->toBeTrue();
});

test('it names the winner of a game by the same ranking the standings use', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create();

    $mp = EventScoreType::factory()->matchPoints()->rankedAt(1)->for($event)->create(['display_order' => 1]);
    $vp = EventScoreType::factory()->victoryPoints()->rankedAt(2)->for($event)->create(['display_order' => 2]);

    $won = EventAttendee::factory()->for($event)->withMember()->create();
    $lost = EventAttendee::factory()->for($event)->withMember()->create();

    $game = Game::factory()->for($round)->create(['table_number' => 1]);
    $game->attendees()->attach([$won->id, $lost->id]);

    // Level on Match Points, so the tiebreaker decides it.
    foreach ([[$won, 3, 85], [$lost, 3, 70]] as [$attendee, $matchPoints, $victoryPoints]) {
        GameScore::factory()->create(['game_id' => $game->id, 'event_attendee_id' => $attendee->id, 'event_score_type_id' => $mp->id, 'value' => $matchPoints]);
        GameScore::factory()->create(['game_id' => $game->id, 'event_attendee_id' => $attendee->id, 'event_score_type_id' => $vp->id, 'value' => $victoryPoints]);
    }

    $attendees = collect($this->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertSuccessful()
        ->json('data.games.0.attendees'))
        ->keyBy('id');

    expect($attendees[$won->id]['is_winner'])->toBeTrue()
        ->and($attendees[$lost->id]['is_winner'])->toBeFalse();
});

test('it calls a game nobody has played a draw rather than picking a winner', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create();

    EventScoreType::factory()->matchPoints()->rankedAt(1)->for($event)->create();

    $game = Game::factory()->for($round)->create(['table_number' => 1]);
    $game->attendees()->attach([
        EventAttendee::factory()->for($event)->withMember()->create()->id,
        EventAttendee::factory()->for($event)->withMember()->create()->id,
    ]);

    $attendees = $this->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertSuccessful()
        ->json('data.games.0.attendees');

    expect(array_column($attendees, 'is_winner'))->toBe([false, false]);
});

test('it counts a bye as a win from the moment it is paired', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create();

    EventScoreType::factory()->matchPoints()->rankedAt(1)->for($event)->create();

    $game = Game::factory()->for($round)->create(['table_number' => null, 'is_bye' => true]);
    $game->attendees()->attach(EventAttendee::factory()->for($event)->withMember()->create());

    $attendees = $this->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertSuccessful()
        ->json('data.games.0.attendees');

    expect($attendees[0]['is_winner'])->toBeTrue();
});
