<?php

use App\Models\User;

test('a user can follow another user', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $this->actingAs($user);

    $this->postJson(route('users.follow', $target))
        ->assertSuccessful()
        ->assertJson([
            'data' => [
                'id' => $target->id,
                'is_following' => true,
                'followers_count' => 1,
            ],
        ]);

    $this->assertDatabaseHas('follows', [
        'follower_id' => $user->id,
        'following_id' => $target->id,
    ]);
});

test('a user cannot follow themselves', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->postJson(route('users.follow', $user))
        ->assertForbidden();
});

test('duplicate follow is idempotent', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $this->actingAs($user);

    $this->postJson(route('users.follow', $target))->assertSuccessful();
    $this->postJson(route('users.follow', $target))
        ->assertSuccessful()
        ->assertJson([
            'data' => [
                'followers_count' => 1,
            ],
        ]);

    expect($target->followers()->count())->toBe(1);
});

test('a user can unfollow another user', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $user->following()->attach($target);
    $this->actingAs($user);

    $this->deleteJson(route('users.unfollow', $target))
        ->assertSuccessful()
        ->assertJson([
            'data' => [
                'id' => $target->id,
                'is_following' => false,
                'followers_count' => 0,
            ],
        ]);

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $user->id,
        'following_id' => $target->id,
    ]);
});

test('unfollowing someone not followed is idempotent', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $this->actingAs($user);

    $this->deleteJson(route('users.unfollow', $target))
        ->assertSuccessful();
});
