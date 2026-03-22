<?php

use App\Http\Resources\Users\UserCardResource;
use App\Models\User;

beforeEach(function () {
    UserCardResource::resetAuthFollowing();
});

test('list followers returns paginated results with correct fields', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create();
    $follower->following()->attach($user);
    $this->actingAs($user);

    $this->getJson(route('users.followers', $user))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'public_name', 'avatar', 'is_following']],
            'meta',
            'links',
        ])
        ->assertJson([
            'data' => [
                ['id' => $follower->id, 'is_following' => false],
            ],
        ]);
});

test('is_following reflects auth user relationship in followers list', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create();
    $follower->following()->attach($user);
    $user->following()->attach($follower);
    $this->actingAs($user);

    $this->getJson(route('users.followers', $user))
        ->assertSuccessful()
        ->assertJson([
            'data' => [
                ['id' => $follower->id, 'is_following' => true],
            ],
        ]);
});

test('list following returns paginated results', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $user->following()->attach($target);
    $this->actingAs($user);

    $this->getJson(route('users.following', $user))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJson([
            'data' => [
                ['id' => $target->id],
            ],
        ]);
});

test('empty followers list returns empty paginated response', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->getJson(route('users.followers', $user))
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});
