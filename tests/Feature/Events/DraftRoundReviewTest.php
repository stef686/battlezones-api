<?php

use App\Enums\Allegiance;
use App\Enums\RoundStatus;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\Game;
use App\Models\Round;

test('a draft round shows pairings, table numbers and rematch flags', function () {
    $event = pairableEvent();
    $organiser = organiserOf($event);

    [$one, $two, $three, $four] = EventAttendee::factory()->count(4)->for($event)->withMember()->create()->all();

    $played = Round::factory()->for($event)->live()->create(['number' => 1]);
    $rematched = Game::factory()->for($played)->create(['table_number' => 1]);
    $rematched->attendees()->attach([$one->id, $two->id]);
    $other = Game::factory()->for($played)->create(['table_number' => 2]);
    $other->attendees()->attach([$three->id, $four->id]);

    $draft = Round::factory()->for($event)->create(['number' => 2]);
    $repeat = Game::factory()->for($draft)->create(['table_number' => 1]);
    $repeat->attendees()->attach([$one->id, $two->id]);
    $fresh = Game::factory()->for($draft)->create(['table_number' => 2]);
    $fresh->attendees()->attach([$three->id, $one->id]);

    $response = $this->actingAs($organiser)
        ->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $draft->id]))
        ->assertSuccessful();

    $games = collect($response->json('data.games'))->keyBy('table_number');

    expect($response->json('data.status'))->toBe('draft')
        ->and($games[1]['is_rematch'])->toBeTrue()
        ->and($games[2]['is_rematch'])->toBeFalse();
});

/**
 * A Draft Round of two opposed Games: L1 v T1 on table 1, L2 v T2 on table 2.
 *
 * @return array{0: Event, 1: Round, 2: Game, 3: Game, 4: EventAttendee, 5: EventAttendee, 6: EventAttendee, 7: EventAttendee}
 */
function opposedDraftRound(): array
{
    $event = pairableEvent(['requires_opposed_allegiance' => true]);

    $loyalOne = EventAttendee::factory()->for($event)->withMember()->create(['allegiance' => Allegiance::Loyalist]);
    $loyalTwo = EventAttendee::factory()->for($event)->withMember()->create(['allegiance' => Allegiance::Loyalist]);
    $traitorOne = EventAttendee::factory()->for($event)->withMember()->create(['allegiance' => Allegiance::Traitor]);
    $traitorTwo = EventAttendee::factory()->for($event)->withMember()->create(['allegiance' => Allegiance::Traitor]);

    $round = Round::factory()->for($event)->create(['number' => 1]);

    $first = Game::factory()->for($round)->create(['table_number' => 1]);
    $first->attendees()->attach([$loyalOne->id, $traitorOne->id]);

    $second = Game::factory()->for($round)->create(['table_number' => 2]);
    $second->attendees()->attach([$loyalTwo->id, $traitorTwo->id]);

    return [$event, $round, $first, $second, $loyalOne, $loyalTwo, $traitorOne, $traitorTwo];
}

test('a swap exchanges the same-allegiance side and keeps every game opposed', function () {
    [$event, $round, $first, $second, $loyalOne, $loyalTwo, $traitorOne, $traitorTwo] = opposedDraftRound();
    $organiser = organiserOf($event);

    $this->actingAs($organiser)
        ->postJson(route('events.rounds.swap', ['event' => $event->slug, 'round' => $round->id]), [
            'game_ids' => [$first->id, $second->id],
        ])
        ->assertSuccessful();

    expect($first->attendees()->pluck('event_attendees.id')->sort()->values()->all())
        ->toEqual(collect([$loyalOne->id, $traitorTwo->id])->sort()->values()->all())
        ->and($second->attendees()->pluck('event_attendees.id')->sort()->values()->all())
        ->toEqual(collect([$loyalTwo->id, $traitorOne->id])->sort()->values()->all());
});

test('table numbers stay with the game across a swap', function () {
    [$event, $round, $first, $second] = opposedDraftRound();
    $organiser = organiserOf($event);

    $this->actingAs($organiser)
        ->postJson(route('events.rounds.swap', ['event' => $event->slug, 'round' => $round->id]), [
            'game_ids' => [$first->id, $second->id],
        ])
        ->assertSuccessful();

    expect($first->refresh()->table_number)->toBe(1)
        ->and($second->refresh()->table_number)->toBe(2);
});

test('swapping is rejected on a live round', function () {
    [$event, $round, $first, $second] = opposedDraftRound();
    $organiser = organiserOf($event);

    $round->update(['status' => RoundStatus::Live]);

    $this->actingAs($organiser)
        ->postJson(route('events.rounds.swap', ['event' => $event->slug, 'round' => $round->id]), [
            'game_ids' => [$first->id, $second->id],
        ])
        ->assertStatus(422);
});

test('players cannot swap pairings', function () {
    [$event, $round, $first, $second, $loyalOne] = opposedDraftRound();

    $player = $loyalOne->memberships()->first()->user;

    $this->actingAs($player)
        ->postJson(route('events.rounds.swap', ['event' => $event->slug, 'round' => $round->id]), [
            'game_ids' => [$first->id, $second->id],
        ])
        ->assertForbidden();
});

test('a game must be swapped with a different game in the same round', function () {
    [$event, $round, $first] = opposedDraftRound();
    $organiser = organiserOf($event);

    $this->actingAs($organiser)
        ->postJson(route('events.rounds.swap', ['event' => $event->slug, 'round' => $round->id]), [
            'game_ids' => [$first->id, $first->id],
        ])
        ->assertStatus(422);

    $otherRound = Round::factory()->for($event)->create(['number' => 2]);
    $elsewhere = Game::factory()->for($otherRound)->create();

    $this->actingAs($organiser)
        ->postJson(route('events.rounds.swap', ['event' => $event->slug, 'round' => $round->id]), [
            'game_ids' => [$first->id, $elsewhere->id],
        ])
        ->assertStatus(422);
});

test('swapping with a bye moves the bye and keeps the game opposed', function () {
    $event = pairableEvent(['requires_opposed_allegiance' => true]);
    $organiser = organiserOf($event);

    $loyalOne = EventAttendee::factory()->for($event)->withMember()->create(['allegiance' => Allegiance::Loyalist]);
    $loyalTwo = EventAttendee::factory()->for($event)->withMember()->create(['allegiance' => Allegiance::Loyalist]);
    $loyalThree = EventAttendee::factory()->for($event)->withMember()->create(['allegiance' => Allegiance::Loyalist]);
    $traitorOne = EventAttendee::factory()->for($event)->withMember()->create(['allegiance' => Allegiance::Traitor]);
    $traitorTwo = EventAttendee::factory()->for($event)->withMember()->create(['allegiance' => Allegiance::Traitor]);

    $round = Round::factory()->for($event)->create(['number' => 1]);

    $played = Game::factory()->for($round)->create(['table_number' => 1]);
    $played->attendees()->attach([$loyalOne->id, $traitorOne->id]);

    $alsoPlayed = Game::factory()->for($round)->create(['table_number' => 2]);
    $alsoPlayed->attendees()->attach([$loyalTwo->id, $traitorTwo->id]);

    $bye = Game::factory()->for($round)->bye()->create(['table_number' => null]);
    $bye->attendees()->attach($loyalThree->id);

    $this->actingAs($organiser)
        ->postJson(route('events.rounds.swap', ['event' => $event->slug, 'round' => $round->id]), [
            'game_ids' => [$bye->id, $played->id],
        ])
        ->assertSuccessful();

    expect($bye->attendees()->pluck('event_attendees.id')->all())->toEqual([$loyalOne->id])
        ->and($played->attendees()->pluck('event_attendees.id')->sort()->values()->all())
        ->toEqual(collect([$loyalThree->id, $traitorOne->id])->sort()->values()->all())
        ->and($bye->refresh()->is_bye)->toBeTrue()
        ->and($bye->table_number)->toBeNull()
        ->and($played->refresh()->table_number)->toBe(1);
});

test('a bye cannot be moved onto the smaller allegiance', function () {
    $event = pairableEvent(['requires_opposed_allegiance' => true]);
    $organiser = organiserOf($event);

    $loyalOne = EventAttendee::factory()->for($event)->withMember()->create(['allegiance' => Allegiance::Loyalist]);
    $loyalTwo = EventAttendee::factory()->for($event)->withMember()->create(['allegiance' => Allegiance::Loyalist]);
    $loyalThree = EventAttendee::factory()->for($event)->withMember()->create(['allegiance' => Allegiance::Loyalist]);
    $traitorOne = EventAttendee::factory()->for($event)->withMember()->create(['allegiance' => Allegiance::Traitor]);
    $traitorTwo = EventAttendee::factory()->for($event)->withMember()->create(['allegiance' => Allegiance::Traitor]);

    $round = Round::factory()->for($event)->create(['number' => 1]);

    $played = Game::factory()->for($round)->create(['table_number' => 1]);
    $played->attendees()->attach([$loyalOne->id, $traitorOne->id]);

    $alsoPlayed = Game::factory()->for($round)->create(['table_number' => 2]);
    $alsoPlayed->attendees()->attach([$loyalTwo->id, $loyalThree->id]);

    $bye = Game::factory()->for($round)->bye()->create(['table_number' => null]);
    $bye->attendees()->attach($traitorTwo->id);

    $this->actingAs($organiser)
        ->postJson(route('events.rounds.swap', ['event' => $event->slug, 'round' => $round->id]), [
            'game_ids' => [$bye->id, $played->id],
        ])
        ->assertStatus(422)
        ->assertJsonPath('message', 'A Bye has to stay with the Allegiance that has more Attendees, or the Round cannot be paired.');

    expect($bye->attendees()->pluck('event_attendees.id')->all())->toEqual([$traitorTwo->id]);
});

test('a swap that creates a rematch succeeds and is flagged', function () {
    [$event, $round, $first, $second, $loyalOne, $loyalTwo, $traitorOne, $traitorTwo] = opposedDraftRound();
    $organiser = organiserOf($event);

    $earlier = Round::factory()->for($event)->live()->create(['number' => 0]);
    $met = Game::factory()->for($earlier)->create(['table_number' => 1]);
    $met->attendees()->attach([$loyalOne->id, $traitorTwo->id]);

    $response = $this->actingAs($organiser)
        ->postJson(route('events.rounds.swap', ['event' => $event->slug, 'round' => $round->id]), [
            'game_ids' => [$first->id, $second->id],
        ])
        ->assertSuccessful();

    $games = collect($response->json('data.games'))->keyBy('id');

    expect($games[$first->id]['is_rematch'])->toBeTrue()
        ->and($games[$second->id]['is_rematch'])->toBeFalse();
});

test('a swap in an event without allegiances exchanges opponents', function () {
    $event = pairableEvent();
    $organiser = organiserOf($event);

    [$one, $two, $three, $four] = EventAttendee::factory()->count(4)->for($event)->withMember()->create()->all();

    $round = Round::factory()->for($event)->create(['number' => 1]);

    $first = Game::factory()->for($round)->create(['table_number' => 1]);
    $first->attendees()->attach([$one->id, $two->id]);

    $second = Game::factory()->for($round)->create(['table_number' => 2]);
    $second->attendees()->attach([$three->id, $four->id]);

    $this->actingAs($organiser)
        ->postJson(route('events.rounds.swap', ['event' => $event->slug, 'round' => $round->id]), [
            'game_ids' => [$first->id, $second->id],
        ])
        ->assertSuccessful();

    expect($first->attendees()->pluck('event_attendees.id')->sort()->values()->all())
        ->toEqual(collect([$one->id, $four->id])->sort()->values()->all())
        ->and($second->attendees()->pluck('event_attendees.id')->sort()->values()->all())
        ->toEqual(collect([$three->id, $two->id])->sort()->values()->all());
});

test('two byes have nothing to exchange', function () {
    $event = pairableEvent();
    $organiser = organiserOf($event);

    [$one, $two] = EventAttendee::factory()->count(2)->for($event)->withMember()->create()->all();

    $round = Round::factory()->for($event)->create(['number' => 1]);

    $first = Game::factory()->for($round)->bye()->create();
    $first->attendees()->attach($one->id);

    $second = Game::factory()->for($round)->bye()->create();
    $second->attendees()->attach($two->id);

    $this->actingAs($organiser)
        ->postJson(route('events.rounds.swap', ['event' => $event->slug, 'round' => $round->id]), [
            'game_ids' => [$first->id, $second->id],
        ])
        ->assertStatus(422);
});
