<?php

use App\Models\User;

test('current user can load profile data', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('profile'))
        ->assertStatus(200)
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
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'public_name' => $profileUser->name,
            ],
        ]);
});
