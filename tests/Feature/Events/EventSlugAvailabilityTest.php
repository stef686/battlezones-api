<?php

use App\Models\Event;
use App\Models\User;

test('a signed-in user is told a slug nobody holds is free', function () {
    $this->actingAs(User::factory()->create())
        ->getJson(route('event-slugs.availability', ['slug' => 'croydon-doubles']))
        ->assertOk()
        ->assertExactJson(['data' => ['slug' => 'croydon-doubles', 'available' => true]]);
});

test('a slug held by a draft is taken, and nothing else about that event is said', function () {
    Event::factory()->draft()->create(['slug' => 'croydon-doubles', 'name' => 'Secret Doubles']);

    $this->actingAs(User::factory()->create())
        ->getJson(route('event-slugs.availability', ['slug' => 'croydon-doubles']))
        ->assertOk()
        ->assertExactJson(['data' => ['slug' => 'croydon-doubles', 'available' => false]]);
});

test('a guest cannot ask whether a slug is free', function () {
    $this->getJson(route('event-slugs.availability', ['slug' => 'croydon-doubles']))
        ->assertUnauthorized();
});

test('a slug the create would refuse is refused here with the same words, not called free', function (string $slug, string $reason) {
    $this->actingAs(User::factory()->create())
        ->getJson(route('event-slugs.availability', ['slug' => $slug]))
        ->assertJsonValidationErrors(['slug' => $reason]);
})->with('refused event slugs');
