<?php

use App\Models\Club;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\Faction;
use App\Models\User;

test('it returns paginated attendees with members, factions and clubs', function () {
    $event = Event::factory()->published()->create();
    $faction = Faction::factory()->create(['name' => 'Space Marines']);
    $club = Club::factory()->create(['name' => 'London Warlords']);

    $user = User::factory()->create(['name' => 'Alice Example']);
    $user->clubs()->attach($club);

    EventAttendee::factory()->for($event)->withMember($user, ['faction_id' => $faction->id])->create();

    $this->getJson(route('events.attendees.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonStructure(['data', 'links', 'meta'])
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Alice Example')
        ->assertJsonPath('data.0.members.0.name', 'Alice Example')
        ->assertJsonPath('data.0.members.0.faction.name', 'Space Marines')
        ->assertJsonPath('data.0.members.0.clubs.0.name', 'London Warlords')
        ->assertJsonPath('data.0.members.0.clubs.0.id', $club->id);
});

test('it returns 404 for non-publicly-visible events', function (string $state) {
    $event = Event::factory()->{$state}()->create();

    $this->getJson(route('events.attendees.index', ['event' => $event->slug]))
        ->assertNotFound();
})->with(['draft', 'cancelled']);

test('it returns 404 for a nonexistent event slug', function () {
    $this->getJson(route('events.attendees.index', ['event' => 'does-not-exist']))
        ->assertNotFound();
});

test('it orders attendees alphabetically by the name they compete under', function () {
    $event = Event::factory()->published()->create();

    foreach (['Charlie', 'Alice', 'Bob'] as $name) {
        EventAttendee::factory()
            ->for($event)
            ->withMember(User::factory()->create(['name' => $name]))
            ->create();
    }

    $response = $this->getJson(route('events.attendees.index', ['event' => $event->slug]))
        ->assertSuccessful();

    expect(collect($response->json('data'))->pluck('name')->all())
        ->toEqual(['Alice', 'Bob', 'Charlie']);
});

test('it does not leak attendees from other events', function () {
    $event = Event::factory()->published()->create();
    $otherEvent = Event::factory()->published()->create();

    EventAttendee::factory()->for($event)->withMember()->create();
    EventAttendee::factory()->for($otherEvent)->withMember()->count(3)->create();

    $this->getJson(route('events.attendees.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('it is a public endpoint requiring no auth', function () {
    $event = Event::factory()->published()->create();

    $this->getJson(route('events.attendees.index', ['event' => $event->slug]))
        ->assertSuccessful();
});

test('it searches by faction name', function () {
    $event = Event::factory()->published()->create();

    $match = Faction::factory()->create(['name' => 'Necrons']);
    $miss = Faction::factory()->create(['name' => 'Tyranids']);

    EventAttendee::factory()->for($event)->withMember(null, ['faction_id' => $match->id])->create();
    EventAttendee::factory()->for($event)->withMember(null, ['faction_id' => $miss->id])->create();

    $this->getJson(route('events.attendees.index', ['event' => $event->slug, 'search' => 'necr']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.members.0.faction.name', 'Necrons');
});

test('it searches by club name', function () {
    $event = Event::factory()->published()->create();

    $matchClub = Club::factory()->create(['name' => 'London Warlords']);
    $missClub = Club::factory()->create(['name' => 'Manchester Marauders']);

    $matchUser = User::factory()->create();
    $matchUser->clubs()->attach($matchClub);
    $missUser = User::factory()->create();
    $missUser->clubs()->attach($missClub);

    EventAttendee::factory()->for($event)->withMember($matchUser)->create();
    EventAttendee::factory()->for($event)->withMember($missUser)->create();

    $this->getJson(route('events.attendees.index', ['event' => $event->slug, 'search' => 'warlords']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.members.0.clubs.0.name', 'London Warlords');
});

test('it returns an empty result when search matches nothing', function () {
    $event = Event::factory()->published()->create();
    EventAttendee::factory()->for($event)->withMember()->count(3)->create();

    $this->getJson(route('events.attendees.index', ['event' => $event->slug, 'search' => 'zzznomatchzzz']))
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

test('search does not leak attendees from other events', function () {
    $event = Event::factory()->published()->create();
    $otherEvent = Event::factory()->published()->create();

    $targetUser = User::factory()->create(['name' => 'Target Person']);
    EventAttendee::factory()->for($otherEvent)->withMember($targetUser)->create();

    $this->getJson(route('events.attendees.index', ['event' => $event->slug, 'search' => 'target']))
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

test('it rejects a non-string search param', function () {
    $event = Event::factory()->published()->create();

    $this->getJson(route('events.attendees.index', ['event' => $event->slug, 'search' => ['not', 'a', 'string']]))
        ->assertUnprocessable();
});

test('it searches by user name case-insensitively', function () {
    $event = Event::factory()->published()->create();

    $match = User::factory()->create(['name' => 'Alice Anderson']);
    $miss = User::factory()->create(['name' => 'Bob Brown']);

    EventAttendee::factory()->for($event)->withMember($match)->create();
    EventAttendee::factory()->for($event)->withMember($miss)->create();

    $this->getJson(route('events.attendees.index', ['event' => $event->slug, 'search' => 'ALICE']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.members.0.name', 'Alice Anderson');
});
