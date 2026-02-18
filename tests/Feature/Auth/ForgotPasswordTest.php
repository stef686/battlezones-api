<?php

use App\Models\User;
use App\Notifications\Auth\ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;

test('a user can request a password reset for a registered email', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->postJson(route('password.email'), ['email' => $user->email])
        ->assertOk()
        ->assertJsonFragment(['message' => 'If a user with that email address exists, we have sent a password reset link. Please check your email.']);

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

test('a password reset request for an unregistered email returns the same generic response', function () {
    Notification::fake();

    $this->postJson(route('password.email'), ['email' => 'nobody@example.com'])
        ->assertOk()
        ->assertJsonFragment(['message' => 'If a user with that email address exists, we have sent a password reset link. Please check your email.']);

    Notification::assertNothingSent();
});

test('a password reset request fails without an email', function () {
    $this->postJson(route('password.email'))
        ->assertJsonValidationErrors(['email']);
});

test('a password reset request fails with an invalid email format', function () {
    $this->postJson(route('password.email'), ['email' => 'not-an-email'])
        ->assertJsonValidationErrors(['email']);
});

test('a password reset token is stored in the database for a registered email', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->postJson(route('password.email'), ['email' => $user->email]);

    $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
});
