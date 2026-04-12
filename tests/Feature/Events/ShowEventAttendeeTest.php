<?php

use App\Models\Club;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\Faction;
use App\Models\User;

test('it returns attendee detail with user, faction, clubs, army_list, checked_in_at and empty games', function () {
    $event = Event::factory()->published()->create();
    $faction = Faction::factory()->create(['name' => 'Space Marines']);
    $club = Club::factory()->create(['name' => 'London Warlords']);

    $user = User::factory()->create(['name' => 'Alice Example']);
    $user->clubs()->attach($club);

    $attendee = EventAttendee::factory()
        ->for($event)
        ->for($user)
        ->for($faction)
        ->create([
            'army_list' => '1500pts Ultramarines list...',
            'checked_in_at' => '2026-04-12 09:30:00',
        ]);

    $this->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $attendee->id]))
        ->assertSuccessful()
        ->assertJsonPath('data.id', $attendee->id)
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.name', 'Alice Example')
        ->assertJsonPath('data.faction.name', 'Space Marines')
        ->assertJsonPath('data.clubs.0.id', $club->id)
        ->assertJsonPath('data.clubs.0.name', 'London Warlords')
        ->assertJsonPath('data.army_list', '1500pts Ultramarines list...')
        ->assertJsonPath('data.checked_in_at', '2026-04-12T09:30:00Z')
        ->assertJsonPath('data.games', []);
});

test('games is always an explicit empty array until games are implemented', function () {
    $event = Event::factory()->published()->create();
    $attendee = EventAttendee::factory()->for($event)->create();

    $response = $this->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $attendee->id]))
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['games']]);

    expect($response->json('data.games'))->toBe([]);
});

test('it returns 404 when the attendee belongs to a different event', function () {
    $event = Event::factory()->published()->create();
    $otherEvent = Event::factory()->published()->create();

    $attendee = EventAttendee::factory()->for($otherEvent)->create();

    $this->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $attendee->id]))
        ->assertNotFound();
});

test('it returns 404 for non-publicly-visible events', function (string $state) {
    $event = Event::factory()->{$state}()->create();
    $attendee = EventAttendee::factory()->for($event)->create();

    $this->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $attendee->id]))
        ->assertNotFound();
})->with(['draft', 'cancelled']);

test('it returns 404 for a nonexistent attendee id', function () {
    $event = Event::factory()->published()->create();

    $this->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => 999999]))
        ->assertNotFound();
});

test('it is a public endpoint requiring no auth', function () {
    $event = Event::factory()->published()->create();
    $attendee = EventAttendee::factory()->for($event)->create();

    $this->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $attendee->id]))
        ->assertSuccessful();
});
