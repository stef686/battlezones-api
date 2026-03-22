<?php

use App\Models\User;

test('current user can load profile data', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('profile'))
        ->assertOk()
        ->assertJson([
            'data' => [
                'public_name' => $user->name,
            ],
        ]);
});

test('a user can load another user\'s profile data', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $profileUser = User::factory()->create();

    $this->get(route('profile.show', $profileUser))
        ->assertOk()
        ->assertJson([
            'data' => [
                'public_name' => $profileUser->name,
            ],
        ]);
});

test('profile followers and following counts reflect actual data', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create();
    $following = User::factory()->create();
    $follower->following()->attach($user);
    $user->following()->attach($following);
    $this->actingAs($user);

    $this->get(route('profile'))
        ->assertSuccessful()
        ->assertJson([
            'data' => [
                'followers_count' => 1,
                'following_count' => 1,
            ],
        ]);
});

test('is_following is present on another user\'s profile', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $user->following()->attach($other);
    $this->actingAs($user);

    $this->get(route('profile.show', $other))
        ->assertSuccessful()
        ->assertJson([
            'data' => [
                'is_following' => true,
            ],
        ]);
});

test('is_following is absent on own profile', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('profile'))
        ->assertSuccessful()
        ->assertJsonMissing(['is_following']);
});
