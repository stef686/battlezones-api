<?php

use App\Models\Photo;
use App\Models\Reaction;
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

test('it includes has_reacted for the authenticated user', function () {
    $user = User::factory()->create();
    $reactedPhoto = Photo::factory()->for($user)->create();
    $unreactedPhoto = Photo::factory()->for($user)->create();

    Reaction::factory()->for($reactedPhoto, 'reactable')->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->getJson(route('gallery.index'))
        ->assertSuccessful();

    $data = collect($response->json('data'));
    $reacted = $data->firstWhere('id', $reactedPhoto->id);
    $unreacted = $data->firstWhere('id', $unreactedPhoto->id);

    expect($reacted['has_reacted'])->toBeTrue()
        ->and($unreacted['has_reacted'])->toBeFalse();
});
