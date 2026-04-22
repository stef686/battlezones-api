<?php

use App\Models\Event;
use App\Models\EventUpdate;
use App\Models\EventUpdateAttachment;
use App\Models\User;

test('it returns updates for a publicly visible event', function () {
    $event = Event::factory()->published()->create();
    EventUpdate::factory()->count(3)->for($event)->create();

    $this->getJson(route('events.updates.index', $event))
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('it is a public endpoint requiring no auth', function () {
    $event = Event::factory()->published()->create();

    $this->getJson(route('events.updates.index', $event))->assertSuccessful();
});

test('it paginates results', function () {
    $event = Event::factory()->published()->create();
    EventUpdate::factory()->count(20)->for($event)->create();

    $this->getJson(route('events.updates.index', $event))
        ->assertSuccessful()
        ->assertJsonStructure(['data', 'links', 'meta']);
});

test('it orders pinned updates before others', function () {
    $event = Event::factory()->published()->create();

    $older = EventUpdate::factory()->for($event)->create([
        'published_at' => now()->subDays(5),
    ]);
    $newer = EventUpdate::factory()->for($event)->create([
        'published_at' => now()->subDay(),
    ]);
    $pinned = EventUpdate::factory()->pinned()->for($event)->create([
        'published_at' => now()->subDays(10),
    ]);

    $response = $this->getJson(route('events.updates.index', $event))
        ->assertSuccessful();

    expect($response->json('data.0.id'))->toBe($pinned->id)
        ->and($response->json('data.1.id'))->toBe($newer->id)
        ->and($response->json('data.2.id'))->toBe($older->id);
});

test('it orders non-pinned updates by published_at descending', function () {
    $event = Event::factory()->published()->create();

    $first = EventUpdate::factory()->for($event)->create([
        'published_at' => now()->subDays(3),
    ]);
    $latest = EventUpdate::factory()->for($event)->create([
        'published_at' => now(),
    ]);
    $middle = EventUpdate::factory()->for($event)->create([
        'published_at' => now()->subDay(),
    ]);

    $response = $this->getJson(route('events.updates.index', $event))
        ->assertSuccessful();

    expect($response->json('data.0.id'))->toBe($latest->id)
        ->and($response->json('data.1.id'))->toBe($middle->id)
        ->and($response->json('data.2.id'))->toBe($first->id);
});

test('it includes the author id and name', function () {
    $event = Event::factory()->published()->create();
    $author = User::factory()->create(['name' => 'Jane Organiser']);
    EventUpdate::factory()->for($event)->for($author, 'author')->create();

    $this->getJson(route('events.updates.index', $event))
        ->assertSuccessful()
        ->assertJsonPath('data.0.author.id', $author->id)
        ->assertJsonPath('data.0.author.name', 'Jane Organiser');
});

test('it includes attachments ordered by display_order', function () {
    $event = Event::factory()->published()->create();
    $update = EventUpdate::factory()->for($event)->create();

    EventUpdateAttachment::factory()->for($update)->create([
        'name' => 'Second',
        'display_order' => 2,
    ]);
    EventUpdateAttachment::factory()->for($update)->create([
        'name' => 'First',
        'display_order' => 1,
    ]);

    $response = $this->getJson(route('events.updates.index', $event))
        ->assertSuccessful()
        ->assertJsonCount(2, 'data.0.attachments')
        ->assertJsonPath('data.0.attachments.0.name', 'First')
        ->assertJsonPath('data.0.attachments.1.name', 'Second');

    $attachment = $response->json('data.0.attachments.0');
    expect($attachment)->toHaveKeys(['id', 'name', 'url', 'display_order']);
});

test('it scopes updates to the requested event', function () {
    $event = Event::factory()->published()->create();
    $otherEvent = Event::factory()->published()->create();

    EventUpdate::factory()->count(2)->for($event)->create();
    EventUpdate::factory()->count(3)->for($otherEvent)->create();

    $this->getJson(route('events.updates.index', $event))
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

test('it returns 404 for a draft event', function () {
    $event = Event::factory()->draft()->create();

    $this->getJson(route('events.updates.index', $event))->assertNotFound();
});

test('it returns 404 for a cancelled event', function () {
    $event = Event::factory()->cancelled()->create();

    $this->getJson(route('events.updates.index', $event))->assertNotFound();
});

test('it returns 404 for a non-existent slug', function () {
    $this->getJson(route('events.updates.index', 'does-not-exist'))->assertNotFound();
});
