<?php

use App\Jobs\SendVerificationEmail;
use App\Models\User;

test('a new user can register for an account', function () {
    Queue::fake([
        SendVerificationEmail::class,
    ]);

    $this->postJson(route('register'), [
        'name' => 'John Doe',
        'email' => 'test@test.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'device_name' => 'test',
    ])
        ->assertSuccessful();

    Queue::assertPushed(SendVerificationEmail::class);

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'test@test.com',
    ]);
});

test('a new user cannot register for an account with missing required data', function () {
    $this->postJson(route('register'))
        ->assertJsonValidationErrors(['name', 'email', 'password', 'device_name']);
});

test('a new user cannot register for an account with mismatching passwords', function () {
    $this->postJson(route('register'), [
        'name' => 'Cannot Register',
        'email' => 'test@test.com',
        'password' => 'password',
        'password_confirmation' => 'different-password',
        'device_name' => 'test',
    ])
        ->assertJsonValidationErrors(['password']);

    $this->assertDatabaseMissing('users', [
        'name' => 'Cannot Register',
    ]);
});

test('a new user cannot register for an account with the same email as an existing user', function () {
    User::factory()->create([
        'email' => 'test@test.com',
    ]);

    $this->postJson(route('register'), [
        'name' => 'Cannot Register',
        'email' => 'test@test.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'device_name' => 'test',
    ])
        ->assertJsonValidationErrors(['email']);

    $this->assertDatabaseMissing('users', [
        'name' => 'Cannot Register',
    ]);
});
