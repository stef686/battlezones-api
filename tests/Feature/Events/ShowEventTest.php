<?php

use App\Models\Event;
use App\Models\EventDocument;

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
