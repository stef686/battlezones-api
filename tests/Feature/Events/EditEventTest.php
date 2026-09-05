<?php

use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventScheduleBlock;
use App\Models\GameSystem;
use App\Models\User;

test('an organiser edits the event a player reads', function () {
    $event = Event::factory()->published()->create([
        'name' => 'London Grand Tournament',
        'venue_city' => 'London',
    ]);
    $organiser = organiserOf($event);

    $this->actingAs($organiser)
        ->patchJson(route('events.update', ['event' => $event->slug]), [
            'name' => 'London Grand Tournament 2027',
            'venue_city' => 'Croydon',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'London Grand Tournament 2027')
        ->assertJsonPath('data.venue.city', 'Croydon');

    expect($event->refresh()->name)->toBe('London Grand Tournament 2027')
        ->and($event->venue_city)->toBe('Croydon');
});

test('the fields that would break credentials already sent, or the way rounds are made, are refused whatever the event', function () {
    $event = Event::factory()->published()->create([
        'slug' => 'london-grand-tournament',
        'attendee_size' => 2,
    ]);
    $organiser = organiserOf($event);

    $this->actingAs($organiser)
        ->patchJson(route('events.update', ['event' => $event->slug]), [
            'slug' => 'something-else',
            'status' => 'draft',
            'pairing_format' => 'random',
        ])
        ->assertJsonValidationErrors(['slug', 'status', 'pairing_format']);

    expect($event->refresh()->slug)->toBe('london-grand-tournament');
});

test('the shape of an event is locked the moment somebody has entered', function () {
    $event = Event::factory()->published()->create(['attendee_size' => 2]);
    $system = GameSystem::factory()->create();
    EventAttendee::factory()->for($event)->create();

    $this->actingAs(organiserOf($event))
        ->patchJson(route('events.update', ['event' => $event->slug]), [
            'game_system_id' => $system->id,
            'attendee_size' => 1,
        ])
        ->assertJsonValidationErrors([
            'game_system_id' => 'their factions belong to the system they entered under',
            'attendee_size' => 'every entry was built at the current size',
        ]);

    expect($event->refresh()->attendee_size)->toBe(2)
        ->and($event->game_system_id)->not->toBe($system->id);
});

test('a cap below the parties already entered is refused rather than allowed to over-fill the event', function () {
    $event = Event::factory()->published()->create(['max_attendees' => 32]);
    EventAttendee::factory()->count(3)->for($event)->create();
    $organiser = organiserOf($event);

    $this->actingAs($organiser)
        ->patchJson(route('events.update', ['event' => $event->slug]), ['max_attendees' => 2])
        ->assertJsonValidationErrors(['max_attendees']);

    $this->actingAs($organiser)
        ->patchJson(route('events.update', ['event' => $event->slug]), ['max_attendees' => 3])
        ->assertSuccessful();

    expect($event->refresh()->max_attendees)->toBe(3);
});

test('moving the event leaves every schedule block where the organiser put it', function () {
    $event = Event::factory()->published()->create([
        'timezone' => 'Europe/London',
        'starts_at' => '2027-03-14T09:00:00+00:00',
        'ends_at' => '2027-03-15T18:00:00+00:00',
    ]);
    $block = EventScheduleBlock::factory()->for($event)->create([
        'starts_at' => '2027-03-14T09:00:00+00:00',
        'ends_at' => '2027-03-14T10:00:00+00:00',
    ]);
    $organiser = organiserOf($event);

    $this->actingAs($organiser)
        ->patchJson(route('events.update', ['event' => $event->slug]), [
            'starts_at' => '2027-03-21T09:00:00+00:00',
            'ends_at' => '2027-03-22T18:00:00+00:00',
        ])
        ->assertSuccessful();

    expect($block->refresh()->starts_at->toIso8601String())->toBe('2027-03-14T09:00:00+00:00')
        ->and($block->day())->toBe('2027-03-14');
});

test('only an organiser of this event may edit it', function () {
    $event = Event::factory()->published()->create();
    $stranger = User::factory()->create();

    $this->patchJson(route('events.update', ['event' => $event->slug]), ['name' => 'Mine now'])
        ->assertUnauthorized();

    $this->actingAs($stranger)
        ->patchJson(route('events.update', ['event' => $event->slug]), ['name' => 'Mine now'])
        ->assertForbidden();
});

test('an event the caller may not see answers not found', function () {
    $event = Event::factory()->draft()->create();
    $stranger = User::factory()->create();

    $this->actingAs($stranger)
        ->patchJson(route('events.update', ['event' => $event->slug]), ['name' => 'Mine now'])
        ->assertNotFound();
});

test('an organiser reshapes an event nobody has entered yet', function () {
    $event = Event::factory()->published()->create(['attendee_size' => 1]);
    $system = GameSystem::factory()->create(['name' => 'Horus Heresy', 'slug' => 'horus-heresy']);

    $this->actingAs(organiserOf($event))
        ->patchJson(route('events.update', ['event' => $event->slug]), [
            'game_system_id' => $system->id,
            'attendee_size' => 2,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.game_system.slug', 'horus-heresy')
        ->assertJsonPath('data.attendee_size', 2);

    expect($event->refresh()->attendee_size)->toBe(2)
        ->and($event->game_system_id)->toBe($system->id);
});

test('a party bigger than a table holds, or a game system that does not exist, is refused', function () {
    $event = Event::factory()->published()->create(['attendee_size' => 2]);

    $this->actingAs(organiserOf($event))
        ->patchJson(route('events.update', ['event' => $event->slug]), [
            'attendee_size' => 9,
            'game_system_id' => 9999,
        ])
        ->assertJsonValidationErrors(['attendee_size', 'game_system_id']);

    $this->actingAs(organiserOf($event))
        ->patchJson(route('events.update', ['event' => $event->slug]), ['attendee_size' => 0])
        ->assertJsonValidationErrors(['attendee_size']);
});
