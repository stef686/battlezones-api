<?php

use App\Enums\EventOrganiserRole;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventDocument;
use App\Models\User;

test('it returns a published event by slug', function () {
    $event = Event::factory()->published()->create(['slug' => 'londongt-2026']);

    $this->getJson(route('events.show', $event))
        ->assertSuccessful()
        ->assertJsonPath('data.slug', 'londongt-2026')
        ->assertJsonPath('data.status', 'published');
});

test('it is a public endpoint requiring no auth', function () {
    $event = Event::factory()->active()->create();

    $this->getJson(route('events.show', $event))->assertSuccessful();
});

test('it returns 404 for a draft event', function () {
    $event = Event::factory()->draft()->create();

    $this->getJson(route('events.show', $event))->assertNotFound();
});

test('it returns 404 for a cancelled event', function () {
    $event = Event::factory()->cancelled()->create();

    $this->getJson(route('events.show', $event))->assertNotFound();
});

test('it returns 404 for a non-existent slug', function () {
    $this->getJson(route('events.show', 'does-not-exist'))->assertNotFound();
});

test('it includes documents in the detail response', function () {
    $event = Event::factory()->published()->create();
    EventDocument::factory()->count(2)->for($event)->create();

    $this->getJson(route('events.show', $event))
        ->assertSuccessful()
        ->assertJsonCount(2, 'data.documents');
});

test('it includes game system and venue details', function () {
    $event = Event::factory()->published()->create([
        'venue_name' => 'Example Hall',
        'venue_city' => 'London',
    ]);

    $this->getJson(route('events.show', $event))
        ->assertSuccessful()
        ->assertJsonPath('data.venue.name', 'Example Hall')
        ->assertJsonPath('data.venue.city', 'London')
        ->assertJsonPath('data.game_system.id', $event->game_system_id);
});

test('viewer is null for an anonymous request', function () {
    $event = Event::factory()->published()->create();

    $this->getJson(route('events.show', $event))
        ->assertSuccessful()
        ->assertJsonPath('data.viewer', null);
});

test('an Organiser sees what they may do at the Event', function () {
    $event = Event::factory()->published()->create();
    $organiser = organiserOf($event);

    $this->actingAs($organiser)
        ->getJson(route('events.show', $event))
        ->assertSuccessful()
        ->assertJsonPath('data.viewer.is_organiser', true)
        ->assertJsonPath('data.viewer.is_lead_organiser', true)
        ->assertJsonPath('data.viewer.is_attendee', false)
        ->assertJsonPath('data.viewer.attendee_id', null)
        ->assertJsonPath('data.viewer.permissions.organise', true)
        ->assertJsonPath('data.viewer.permissions.manage_organisers', true);
});

test('an Attendee sees which Attendee they are', function () {
    $event = Event::factory()->published()->create();
    $player = User::factory()->create();
    $attendee = EventAttendee::factory()->for($event)->withMember($player)->create();

    $this->actingAs($player)
        ->getJson(route('events.show', $event))
        ->assertSuccessful()
        ->assertJsonPath('data.viewer.is_attendee', true)
        ->assertJsonPath('data.viewer.attendee_id', $attendee->id)
        ->assertJsonPath('data.viewer.is_organiser', false)
        ->assertJsonPath('data.viewer.permissions.organise', false)
        ->assertJsonPath('data.viewer.permissions.manage_organisers', false);
});

test('viewer permissions follow the policies rather than a copy of them', function () {
    $event = Event::factory()->published()->create(['registration_closes_at' => now()->subDay()]);
    $player = User::factory()->create();

    $this->actingAs($player)
        ->getJson(route('events.show', $event))
        ->assertSuccessful()
        ->assertJsonPath('data.viewer.permissions.register', false);

    $event->forceFill(['registration_closes_at' => now()->addDay()])->save();

    $this->actingAs($player)
        ->getJson(route('events.show', $event))
        ->assertJsonPath('data.viewer.permissions.register', true);
});

test('an appointed Organiser who does not lead cannot manage Organisers', function () {
    $event = Event::factory()->published()->create();
    organiserOf($event);

    $assistant = User::factory()->create();
    $event->organisers()->attach($assistant, ['role' => EventOrganiserRole::Organiser->value]);

    $this->actingAs($assistant)
        ->getJson(route('events.show', $event))
        ->assertSuccessful()
        ->assertJsonPath('data.viewer.is_organiser', true)
        ->assertJsonPath('data.viewer.is_lead_organiser', false)
        ->assertJsonPath('data.viewer.permissions.organise', true)
        ->assertJsonPath('data.viewer.permissions.manage_organisers', false);
});
