<?php

use App\Models\PendingEmailChange;
use App\Models\User;
use App\Notifications\Profile\EmailChangeRequestedNotification;
use App\Notifications\Profile\VerifyNewEmailNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

test('requesting email change sends verification to new email and notification to old email', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'old@example.com']);

    $this->actingAs($user)
        ->postJson(route('profile.email'), ['current_password' => 'password', 'email' => 'new@example.com'])
        ->assertSuccessful()
        ->assertJsonPath('message', 'A verification link has been sent to your new email address.');

    Notification::assertSentOnDemand(VerifyNewEmailNotification::class, function ($notification, $channels, $notifiable) {
        return $notifiable->routes['mail'] === 'new@example.com';
    });

    Notification::assertSentTo($user, EmailChangeRequestedNotification::class);
});

test('requesting email change rejects duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('profile.email'), ['current_password' => 'password', 'email' => 'taken@example.com'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

test('requesting email change rejects wrong password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('profile.email'), ['current_password' => 'wrong', 'email' => 'new@example.com'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('current_password');
});

test('requesting email change rejects missing password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('profile.email'), ['email' => 'new@example.com'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('current_password');
});

test('requesting email change rejects invalid email', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('profile.email'), ['current_password' => 'password', 'email' => 'not-an-email'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

test('valid signed link updates email and sets email_verified_at and revokes tokens', function () {
    $user = User::factory()->create(['email' => 'old@example.com']);
    $token = 'test-token-for-verification';

    PendingEmailChange::query()->create([
        'user_id' => $user->id,
        'email' => 'new@example.com',
        'token' => hash('sha256', $token),
        'created_at' => now(),
    ]);

    $user->createToken('test-token');

    $url = URL::signedRoute('email.change.verify', [
        'user' => $user->id,
        'token' => $token,
    ]);

    $this->get($url)
        ->assertSuccessful()
        ->assertJsonPath('message', 'Your email address has been updated.');

    $user->refresh();
    expect($user->email)->toBe('new@example.com')
        ->and($user->email_verified_at)->not->toBeNull()
        ->and($user->tokens)->toHaveCount(0);
});

test('invalid token returns 403', function () {
    $user = User::factory()->create();

    PendingEmailChange::query()->create([
        'user_id' => $user->id,
        'email' => 'new@example.com',
        'token' => hash('sha256', 'real-token'),
        'created_at' => now(),
    ]);

    $url = URL::signedRoute('email.change.verify', [
        'user' => $user->id,
        'token' => 'wrong-token',
    ]);

    $this->get($url)->assertForbidden();
});

test('old email stays until verification is completed', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'old@example.com']);

    $this->actingAs($user)
        ->postJson(route('profile.email'), ['current_password' => 'password', 'email' => 'new@example.com'])
        ->assertSuccessful();

    expect($user->fresh()->email)->toBe('old@example.com');
});

test('requesting a new email change replaces the previous pending change', function () {
    Notification::fake();

    $user = User::factory()->create();

    PendingEmailChange::query()->create([
        'user_id' => $user->id,
        'email' => 'first@example.com',
        'token' => hash('sha256', 'old-token'),
        'created_at' => now(),
    ]);

    $this->actingAs($user)
        ->postJson(route('profile.email'), ['current_password' => 'password', 'email' => 'second@example.com'])
        ->assertSuccessful();

    expect(PendingEmailChange::query()->where('user_id', $user->id)->count())->toBe(1)
        ->and(PendingEmailChange::query()->where('user_id', $user->id)->first()->email)->toBe('second@example.com');
});
