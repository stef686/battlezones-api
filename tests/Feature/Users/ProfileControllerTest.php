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
