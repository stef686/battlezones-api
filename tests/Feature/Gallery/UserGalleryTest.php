<?php

use App\Models\Photo;
use App\Models\Reaction;
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

test('it includes has_reacted for the authenticated user', function () {
    $user = User::factory()->create();
    $viewer = User::factory()->create();

    $reactedPhoto = Photo::factory()->for($user)->create();
    $unreactedPhoto = Photo::factory()->for($user)->create();

    Reaction::factory()->for($reactedPhoto, 'reactable')->create(['user_id' => $viewer->id]);

    $response = $this->actingAs($viewer)
        ->getJson(route('users.gallery', $user))
        ->assertSuccessful();

    $data = collect($response->json('data'));
    $reacted = $data->firstWhere('id', $reactedPhoto->id);
    $unreacted = $data->firstWhere('id', $unreactedPhoto->id);

    expect($reacted['has_reacted'])->toBeTrue()
        ->and($unreacted['has_reacted'])->toBeFalse();
});
