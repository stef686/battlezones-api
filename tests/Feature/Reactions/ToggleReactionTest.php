<?php

use App\Models\Photo;
use App\Models\User;

test('it creates a reaction on first call', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $this->actingAs($user)
        ->postJson(route('gallery.react', $photo))
        ->assertSuccessful()
        ->assertJsonPath('reactions_count', 1);

    expect($photo->reactions()->where('user_id', $user->id)->exists())->toBeTrue();
});

test('it removes the reaction on second call', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $this->actingAs($user)->postJson(route('gallery.react', $photo));

    $this->actingAs($user)
        ->postJson(route('gallery.react', $photo))
        ->assertSuccessful()
        ->assertJsonPath('reactions_count', 0);

    expect($photo->reactions()->where('user_id', $user->id)->exists())->toBeFalse();
});

test('it returns the updated count with multiple users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $photo = Photo::factory()->create();

    $this->actingAs($user1)->postJson(route('gallery.react', $photo));

    $this->actingAs($user2)
        ->postJson(route('gallery.react', $photo))
        ->assertSuccessful()
        ->assertJsonPath('reactions_count', 2);
});
