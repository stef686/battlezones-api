<?php

use App\Models\PendingPasswordChange;
use App\Models\User;
use App\Notifications\Profile\ConfirmPasswordChangeNotification;
use App\Services\Frontend;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

test('wrong current password returns validation error', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('profile.password'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('current_password');
});

test('missing confirmation returns validation error', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'new-password',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');
});

test('mismatched confirmation returns validation error', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');
});

test('requesting password change sends confirmation notification', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertSuccessful()
        ->assertJsonPath('message', 'A confirmation link has been sent to your email address.');

    Notification::assertSentTo($user, ConfirmPasswordChangeNotification::class);
});

test('valid confirmation link within 1 day changes password and revokes tokens', function () {
    $user = User::factory()->create();
    $token = 'test-token-for-password';
    $hashedPassword = Hash::make('new-password');

    PendingPasswordChange::query()->create([
        'user_id' => $user->id,
        'password' => $hashedPassword,
        'token' => hash('sha256', $token),
        'created_at' => now(),
    ]);

    $user->createToken('test-token');

    $url = URL::signedRoute('password.change.confirm', [
        'user' => $user->id,
        'token' => $token,
    ]);

    $this->get($url)
        ->assertRedirect(Frontend::resultUrl(Frontend::PASSWORD_CHANGED_PATH, 'changed'));

    $user->refresh();
    expect(Hash::check('new-password', $user->password))->toBeTrue()
        ->and($user->tokens)->toHaveCount(0);
});

test('expired confirmation link returns 410 and password is unchanged', function () {
    $user = User::factory()->create();
    $token = 'test-token-expired';
    $originalPassword = $user->password;

    PendingPasswordChange::query()->create([
        'user_id' => $user->id,
        'password' => Hash::make('new-password'),
        'token' => hash('sha256', $token),
        'created_at' => now()->subDays(2),
    ]);

    $url = URL::signedRoute('password.change.confirm', [
        'user' => $user->id,
        'token' => $token,
    ]);

    $this->get($url)
        ->assertRedirect(Frontend::resultUrl(Frontend::PASSWORD_CHANGED_PATH, 'expired'));

    expect($user->fresh()->password)->toBe($originalPassword)
        ->and(PendingPasswordChange::query()->where('user_id', $user->id)->count())->toBe(0);
});

test('invalid token returns 403', function () {
    $user = User::factory()->create();

    PendingPasswordChange::query()->create([
        'user_id' => $user->id,
        'password' => Hash::make('new-password'),
        'token' => hash('sha256', 'real-token'),
        'created_at' => now(),
    ]);

    $url = URL::signedRoute('password.change.confirm', [
        'user' => $user->id,
        'token' => 'wrong-token',
    ]);

    $this->get($url)->assertRedirect(Frontend::resultUrl(Frontend::PASSWORD_CHANGED_PATH, 'invalid'));
});

test('requesting a new password change replaces the previous pending change', function () {
    Notification::fake();

    $user = User::factory()->create();

    PendingPasswordChange::query()->create([
        'user_id' => $user->id,
        'password' => Hash::make('old-new-password'),
        'token' => hash('sha256', 'old-token'),
        'created_at' => now(),
    ]);

    $this->actingAs($user)
        ->postJson(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'another-password',
            'password_confirmation' => 'another-password',
        ])
        ->assertSuccessful();

    expect(PendingPasswordChange::query()->where('user_id', $user->id)->count())->toBe(1);
});
