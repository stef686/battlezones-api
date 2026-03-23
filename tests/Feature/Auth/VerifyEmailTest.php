<?php

use App\Models\User;
use Illuminate\Support\Facades\URL;

test('a user can verify their email address', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
    );

    $this->get($verificationUrl)
        ->assertSuccessful()
        ->assertJson(['message' => 'Email verified successfully!']);

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('a user cannot verify their email with an invalid hash', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => 'invalid-hash']
    );

    $this->get($verificationUrl)
        ->assertForbidden()
        ->assertJson(['message' => 'Invalid verification link.']);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('a user cannot verify their email with an invalid signature', function () {
    $user = User::factory()->unverified()->create();

    $this->get("/email/verify/{$user->id}/".sha1($user->getEmailForVerification()).'?signature=invalid')
        ->assertForbidden();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});
