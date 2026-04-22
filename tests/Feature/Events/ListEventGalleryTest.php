<?php

use App\Models\Event;
use App\Models\Photo;
use App\Models\Reaction;
use App\Models\User;

test('it returns paginated photos for an event, latest first', function () {
    $event = Event::factory()->published()->create();
    $user = User::factory()->create();

    $older = Photo::factory()->for($user)->create([
        'event_id' => $event->id,
        'created_at' => now()->subDay(),
    ]);
    $newer = Photo::factory()->for($user)->create([
        'event_id' => $event->id,
        'created_at' => now(),
    ]);

    $this->getJson(route('events.gallery.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonStructure(['data', 'links', 'meta'])
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $newer->id)
        ->assertJsonPath('data.1.id', $older->id);
});

test('it returns 404 for non-publicly-visible events', function (string $state) {
    $event = Event::factory()->{$state}()->create();

    $this->getJson(route('events.gallery.index', ['event' => $event->slug]))
        ->assertNotFound();
})->with(['draft', 'cancelled']);

test('it includes reaction counts', function () {
    $event = Event::factory()->published()->create();
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create(['event_id' => $event->id]);

    Reaction::factory()->count(3)->for($photo, 'reactable')->create();

    $this->getJson(route('events.gallery.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonPath('data.0.reactions_count', 3);
});

test('it includes has_reacted when authenticated', function () {
    $event = Event::factory()->published()->create();
    $user = User::factory()->create();

    $reactedPhoto = Photo::factory()->for($user)->create(['event_id' => $event->id]);
    $unreactedPhoto = Photo::factory()->for($user)->create(['event_id' => $event->id]);

    Reaction::factory()->for($reactedPhoto, 'reactable')->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->getJson(route('events.gallery.index', ['event' => $event->slug]))
        ->assertSuccessful();

    $data = collect($response->json('data'));
    $reacted = $data->firstWhere('id', $reactedPhoto->id);
    $unreacted = $data->firstWhere('id', $unreactedPhoto->id);

    expect($reacted['has_reacted'])->toBeTrue()
        ->and($unreacted['has_reacted'])->toBeFalse();
});

test('it does not include has_reacted when unauthenticated', function () {
    $event = Event::factory()->published()->create();
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create(['event_id' => $event->id]);

    Reaction::factory()->for($photo, 'reactable')->create(['user_id' => $user->id]);

    $response = $this->getJson(route('events.gallery.index', ['event' => $event->slug]))
        ->assertSuccessful();

    expect($response->json('data.0'))->not->toHaveKey('has_reacted');
});

test('it returns empty paginated response when event has no photos', function () {
    $event = Event::factory()->published()->create();

    $this->getJson(route('events.gallery.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonStructure(['data', 'links', 'meta'])
        ->assertJsonCount(0, 'data');
});

test('it does not leak photos from other events', function () {
    $event = Event::factory()->published()->create();
    $otherEvent = Event::factory()->published()->create();
    $user = User::factory()->create();

    Photo::factory()->for($user)->create(['event_id' => $event->id]);
    Photo::factory()->for($user)->create(['event_id' => $otherEvent->id]);
    Photo::factory()->for($user)->create();

    $this->getJson(route('events.gallery.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});
