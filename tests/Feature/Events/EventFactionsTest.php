<?php

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\Faction;
use App\Models\GameSystem;
use App\Models\User;

test('the factions on offer are the ones from this event\'s game system', function () {
    $event = Event::factory()->published()->create();

    $mine = Faction::factory()->create(['game_system_id' => $event->game_system_id, 'name' => 'Sons of Horus']);
    $alsoMine = Faction::factory()->create(['game_system_id' => $event->game_system_id, 'name' => 'Imperial Fists']);
    $anotherGame = Faction::factory()->create(['game_system_id' => GameSystem::factory()->create()->id]);

    $response = $this->getJson(route('events.factions.index', ['event' => $event->slug]))
        ->assertOk();

    expect(collect($response->json('data'))->pluck('id')->all())
        // Alphabetical: a picker in database order is a picker nobody can use.
        ->toBe([$alsoMine->id, $mine->id])
        ->not->toContain($anotherGame->id);
});

test('the factions of an event nobody can see are not readable either', function () {
    $event = Event::factory()->create(['status' => EventStatus::Draft]);

    $this->getJson(route('events.factions.index', ['event' => $event->slug]))
        ->assertNotFound();
});

test('a player records the faction they are bringing', function () {
    $event = Event::factory()->published()->create();
    $faction = Faction::factory()->create(['game_system_id' => $event->game_system_id]);
    $player = User::factory()->create();

    $attendee = EventAttendee::factory()->for($event)->create();
    $attendee->members()->attach($player, ['event_id' => $event->id]);

    $this->actingAs($player)
        ->patchJson(route('events.my-faction.update', ['event' => $event->slug]), ['faction_id' => $faction->id])
        ->assertOk()
        ->assertJsonPath('data.members.0.faction.id', $faction->id);
});

test('a player can withdraw the faction they chose', function () {
    $event = Event::factory()->published()->create();
    $faction = Faction::factory()->create(['game_system_id' => $event->game_system_id]);
    $player = User::factory()->create();

    $attendee = EventAttendee::factory()->for($event)->create();
    $attendee->members()->attach($player, ['event_id' => $event->id, 'faction_id' => $faction->id]);

    $this->actingAs($player)
        ->patchJson(route('events.my-faction.update', ['event' => $event->slug]), ['faction_id' => null])
        ->assertOk()
        ->assertJsonPath('data.members.0.faction', null);
});

test('a faction from another game system is refused', function () {
    $event = Event::factory()->published()->create();
    $elsewhere = Faction::factory()->create(['game_system_id' => GameSystem::factory()->create()->id]);
    $player = User::factory()->create();

    $attendee = EventAttendee::factory()->for($event)->create();
    $attendee->members()->attach($player, ['event_id' => $event->id]);

    $this->actingAs($player)
        ->patchJson(route('events.my-faction.update', ['event' => $event->slug]), ['faction_id' => $elsewhere->id])
        ->assertJsonValidationErrorFor('faction_id');
});

test('a player records only their own faction, never a team mate\'s', function () {
    $event = Event::factory()->published()->create(['attendee_size' => 2]);
    $faction = Faction::factory()->create(['game_system_id' => $event->game_system_id]);

    $player = User::factory()->create();
    $partner = User::factory()->create();

    $attendee = EventAttendee::factory()->for($event)->create();
    $attendee->members()->attach($player, ['event_id' => $event->id]);
    $attendee->members()->attach($partner, ['event_id' => $event->id]);

    $this->actingAs($player)
        ->patchJson(route('events.my-faction.update', ['event' => $event->slug]), ['faction_id' => $faction->id])
        ->assertOk();

    expect($attendee->memberships()->where('user_id', $partner->id)->value('faction_id'))->toBeNull();
});

test('someone who has not entered has no faction to record', function () {
    $event = Event::factory()->published()->create();
    $faction = Faction::factory()->create(['game_system_id' => $event->game_system_id]);

    $this->actingAs(User::factory()->create())
        ->patchJson(route('events.my-faction.update', ['event' => $event->slug]), ['faction_id' => $faction->id])
        ->assertNotFound();
});

test('an unclaimed player who entered on an invite can still say what they are bringing', function () {
    $event = Event::factory()->published()->create();
    $faction = Faction::factory()->create(['game_system_id' => $event->game_system_id]);

    // Unclaimed accounts refuse route binding, so an endpoint addressing this
    // Player by id could not reach them at all.
    $invited = User::factory()->unclaimed()->create();

    $attendee = EventAttendee::factory()->for($event)->create();
    $attendee->members()->attach($invited, ['event_id' => $event->id]);

    $this->actingAs($invited)
        ->patchJson(route('events.my-faction.update', ['event' => $event->slug]), ['faction_id' => $faction->id])
        ->assertOk()
        ->assertJsonPath('data.members.0.faction.id', $faction->id);
});
