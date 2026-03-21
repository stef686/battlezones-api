<?php

use App\Models\Photo;
use App\Models\User;

test('it returns the public gallery for any user', function () {
    $user = User::factory()->create();
    $viewer = User::factory()->create();

    Photo::factory()->count(3)->for($user)->create();

    $this->actingAs($viewer)
        ->getJson(route('users.gallery', $user))
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('it paginates results', function () {
    $user = User::factory()->create();
    $viewer = User::factory()->create();

    Photo::factory()->count(20)->for($user)->create();

    $this->actingAs($viewer)
        ->getJson(route('users.gallery', $user))
        ->assertSuccessful()
        ->assertJsonStructure(['data', 'links', 'meta']);
});
