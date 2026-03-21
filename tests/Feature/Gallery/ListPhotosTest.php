<?php

use App\Models\Photo;
use App\Models\User;

test('it returns the authenticated user photos only', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Photo::factory()->count(3)->for($user)->create();
    Photo::factory()->count(2)->for($otherUser)->create();

    $this->actingAs($user)
        ->getJson(route('gallery.index'))
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('it includes reactions_count and thumbnail_url', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create();

    $this->actingAs($user)
        ->getJson(route('gallery.index'))
        ->assertSuccessful()
        ->assertJsonPath('data.0.reactions_count', 0)
        ->assertJsonPath('data.0.thumbnail_url', $photo->thumbnail_url);
});

test('it paginates results', function () {
    $user = User::factory()->create();
    Photo::factory()->count(20)->for($user)->create();

    $this->actingAs($user)
        ->getJson(route('gallery.index'))
        ->assertSuccessful()
        ->assertJsonStructure(['data', 'links', 'meta']);
});
