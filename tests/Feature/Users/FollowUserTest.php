<?php

use App\Models\User;

test('a user can follow another user', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $this->actingAs($user);

    $this->postJson(route('users.follow', $target))
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['id', 'public_name', 'followers_count', 'following_count', 'is_following']])
        ->assertJsonPath('data.id', $target->id)
        ->assertJsonPath('data.followers_count', 1)
        ->assertJsonPath('data.is_following', true);

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
        ->assertJsonPath('data.followers_count', 1);

    expect($target->followers()->count())->toBe(1);
});

test('a user can unfollow another user', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $user->following()->attach($target);
    $this->actingAs($user);

    $this->deleteJson(route('users.unfollow', $target))
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['id', 'public_name', 'followers_count', 'following_count', 'is_following']])
        ->assertJsonPath('data.id', $target->id)
        ->assertJsonPath('data.followers_count', 0)
        ->assertJsonPath('data.is_following', false);

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

test('deleting a user cascades to their follow records', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $user->following()->attach($target);
    $target->following()->attach($user);

    $user->delete();

    $this->assertDatabaseMissing('follows', ['follower_id' => $user->id]);
    $this->assertDatabaseMissing('follows', ['following_id' => $user->id]);
});
