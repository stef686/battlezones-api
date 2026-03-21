<?php

use App\Models\Photo;
use App\Models\Reaction;
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
        ->assertJsonPath('data.reactions_count', 0)
        ->assertJsonPath('data.has_reacted', false);
});

test('it returns has_reacted true when user has reacted', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create();

    Reaction::factory()->for($photo, 'reactable')->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->getJson(route('gallery.show', $photo))
        ->assertSuccessful()
        ->assertJsonPath('data.has_reacted', true);
});

test('it returns 404 for missing photo', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('gallery.show', 999))
        ->assertNotFound();
});
