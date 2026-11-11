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

test('an account nobody has claimed cannot ask, since it could never create an event there', function () {
    $this->actingAs(User::factory()->unclaimed()->create())
        ->getJson(route('event-slugs.availability', ['slug' => 'croydon-doubles']))
        ->assertForbidden();
});

test('one account is limited in how fast it can ask, so drafts cannot be found by guessing at speed', function () {
    $prober = User::factory()->create();

    for ($i = 0; $i < 30; $i++) {
        $this->actingAs($prober)
            ->getJson(route('event-slugs.availability', ['slug' => "guess-{$i}"]))
            ->assertOk();
    }

    $this->actingAs($prober)
        ->getJson(route('event-slugs.availability', ['slug' => 'guess-final']))
        ->assertTooManyRequests();

    // The budget is the account's, not the venue wifi's.
    $this->actingAs(User::factory()->create())
        ->getJson(route('event-slugs.availability', ['slug' => 'croydon-doubles']))
        ->assertOk();
});
