<?php

use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventScheduleBlock;
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

test('the fields that would break credentials already sent, or registrations already taken, are refused', function () {
    $event = Event::factory()->published()->create([
        'slug' => 'london-grand-tournament',
        'attendee_size' => 2,
    ]);
    $organiser = organiserOf($event);

    $this->actingAs($organiser)
        ->patchJson(route('events.update', ['event' => $event->slug]), [
            'slug' => 'something-else',
            'attendee_size' => 1,
            'status' => 'draft',
            'pairing_format' => 'random',
        ])
        ->assertJsonValidationErrors(['slug', 'attendee_size', 'status', 'pairing_format']);

    expect($event->refresh()->slug)->toBe('london-grand-tournament')
        ->and($event->attendee_size)->toBe(2);
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
