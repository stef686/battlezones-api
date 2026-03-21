<?php

use App\Models\Photo;
use App\Models\User;

test('it returns photo details with url', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create();

    $this->actingAs($user)
        ->getJson(route('gallery.show', $photo))
        ->assertSuccessful()
        ->assertJsonPath('data.id', $photo->id)
        ->assertJsonPath('data.name', $photo->name)
        ->assertJsonPath('data.url', $photo->url)
        ->assertJsonPath('data.reactions_count', 0);
});

test('it returns 404 for missing photo', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('gallery.show', 999))
        ->assertNotFound();
});
