<?php

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\GameScore;
use Database\Seeders\EventSeeder;

test('it runs without errors', function () {
    $this->seed(EventSeeder::class);

    expect(Event::count())->toBeGreaterThanOrEqual(5);
});

test('it creates events across all statuses', function () {
    $this->seed(EventSeeder::class);

    foreach (EventStatus::cases() as $status) {
        expect(Event::where('status', $status)->exists())
            ->toBeTrue("Missing event with status: {$status->value}");
    }
});

test('active event has attendees, rounds, games, and scores', function () {
    $this->seed(EventSeeder::class);

    $event = Event::where('status', EventStatus::Active)->first();

    expect($event->attendees()->count())->toBe(16)
        ->and($event->rounds()->count())->toBe(3)
        ->and($event->rounds()->first()->games()->count())->toBeGreaterThan(0)
        ->and(GameScore::query()
            ->whereHas('game', fn ($q) => $q->whereHas('round', fn ($q) => $q->where('event_id', $event->id)))
            ->count()
        )->toBeGreaterThan(0);

    $this->getJson(route('events.standings.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonPath('meta.total', 16);
});

test('completed event has standings for all attendees', function () {
    $this->seed(EventSeeder::class);

    $event = Event::where('status', EventStatus::Completed)->first();
    $attendeeCount = $event->attendees()->count();

    expect($attendeeCount)->toBeGreaterThan(0);

    $response = $this->getJson(route('events.standings.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonPath('meta.total', $attendeeCount);

    expect($response->json('data.0.scores'))->toHaveCount(2)
        ->and($response->json('data.0.position'))->toBe(1);
});

test('gallery endpoint returns photos for seeded events', function () {
    $this->seed(EventSeeder::class);

    $event = Event::where('status', EventStatus::Active)->first();

    $this->getJson(route('events.gallery.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonStructure(['data'])
        ->assertJsonPath('data.0.reactions_count', fn ($v) => $v >= 0);
});

test('event endpoints return meaningful data after seeding', function () {
    $this->seed(EventSeeder::class);

    $event = Event::where('status', EventStatus::Active)->first();

    $this->getJson(route('events.show', $event))
        ->assertSuccessful()
        ->assertJsonPath('data.documents', fn ($docs) => count($docs) >= 1);

    $this->getJson(route('events.updates.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonPath('data', fn ($updates) => count($updates) >= 1);

    $this->getJson(route('events.attendees.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonPath('data', fn ($attendees) => count($attendees) >= 1);

    $this->getJson(route('events.standings.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonPath('data', fn ($standings) => count($standings) >= 1);
});
