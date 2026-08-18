<?php

use App\Models\User;

test('an unclaimed account is not found by user search', function () {
    $searcher = User::factory()->create();
    User::factory()->create(['name' => 'Horus Lupercal', 'username' => 'horus-claimed']);
    User::factory()->unclaimed()->create(['name' => 'Horus Aximand', 'username' => 'horus-invited']);

    $this->actingAs($searcher)
        ->getJson(route('users.search', ['q' => 'horus']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.username', 'horus-claimed');
});

test('an unclaimed account has no public profile', function () {
    $viewer = User::factory()->create();
    $invited = User::factory()->unclaimed()->create();

    $this->actingAs($viewer)
        ->getJson(route('profile.show', ['user' => $invited->id]))
        ->assertNotFound();
});

test('an unclaimed account cannot be followed', function () {
    $follower = User::factory()->create();
    $invited = User::factory()->unclaimed()->create();

    $this->actingAs($follower)
        ->postJson(route('users.follow', ['user' => $invited->id]))
        ->assertNotFound();

    expect($follower->following()->count())->toBe(0);
});

test('an unclaimed account is left out of follower listings', function () {
    $viewer = User::factory()->create();
    $followed = User::factory()->create();
    $claimedFollower = User::factory()->create();
    $invitedFollower = User::factory()->unclaimed()->create();

    $followed->followers()->attach([$claimedFollower->id, $invitedFollower->id]);

    $this->actingAs($viewer)
        ->getJson(route('users.followers', ['user' => $followed->id]))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $claimedFollower->id);
});
