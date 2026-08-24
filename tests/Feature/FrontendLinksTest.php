<?php

use App\Enums\EventInviteRole;
use App\Models\Event;
use App\Models\PendingEmailChange;
use App\Models\PendingPasswordChange;
use App\Models\User;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Events\EventInviteNotification;
use App\Notifications\Events\FeedbackRequestNotification;
use App\Services\Frontend;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    config(['app.frontend_url' => 'https://battlezones.app']);
});

test('the frontend url is configured and has no trailing slash', function () {
    config(['app.frontend_url' => 'https://battlezones.app/']);

    expect(Frontend::url('/invites/abc'))->toBe('https://battlezones.app/invites/abc');
});

test('a frontend link carries its query string', function () {
    expect(Frontend::url('/reset-password', ['token' => 'abc', 'email' => 'ada@example.com']))
        ->toBe('https://battlezones.app/reset-password?token=abc&email=ada%40example.com');
});

test('an invitation email opens the SPA rather than an API endpoint', function () {
    $event = Event::factory()->create();
    $user = User::factory()->create();

    $mail = (new EventInviteNotification($event, EventInviteRole::Captain, 'a-token'))->toMail($user);

    expect($mail->actionUrl)->toBe('https://battlezones.app/invites/a-token');
});

test('a feedback email opens the SPA rather than an API endpoint', function () {
    $event = Event::factory()->create();
    $user = User::factory()->create();

    $mail = (new FeedbackRequestNotification($event, 'a-token'))->toMail($user);

    expect($mail->actionUrl)->toBe('https://battlezones.app/feedback/a-token');
});

test('a password reset email opens the SPA rather than an API endpoint', function () {
    $user = User::factory()->create(['email' => 'ada@example.com']);

    $mail = (new ResetPasswordNotification('a-token'))->toMail($user);

    expect($mail->actionUrl)
        ->toBe('https://battlezones.app/reset-password?token=a-token&email=ada%40example.com');
});

test('invite and feedback links sit at the top level, so a link association can cover them', function () {
    expect(Frontend::url(Frontend::INVITE_PATH.'/a-token'))->toBe('https://battlezones.app/invites/a-token')
        ->and(Frontend::url(Frontend::FEEDBACK_PATH.'/a-token'))->toBe('https://battlezones.app/feedback/a-token');
});

test('a verified email lands the reader back in the SPA', function () {
    $user = User::factory()->unverified()->create();

    $this->get(URL::temporarySignedRoute('verification.verify', now()->addHour(), [
        'id' => $user->id,
        'hash' => sha1($user->getEmailForVerification()),
    ]))->assertRedirect(Frontend::url(Frontend::EMAIL_VERIFIED_PATH, ['status' => 'verified']));

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('a bad verification hash lands in the SPA saying so', function () {
    $user = User::factory()->unverified()->create();

    $this->get(URL::temporarySignedRoute('verification.verify', now()->addHour(), [
        'id' => $user->id,
        'hash' => 'invalid-hash',
    ]))->assertRedirect(Frontend::url(Frontend::EMAIL_VERIFIED_PATH, ['status' => 'invalid']));

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('a confirmed email change lands the reader back in the SPA', function () {
    $user = User::factory()->create(['email' => 'old@example.com']);
    $token = 'a-token';

    PendingEmailChange::query()->create([
        'user_id' => $user->id,
        'email' => 'new@example.com',
        'token' => hash('sha256', $token),
        'created_at' => now(),
    ]);

    $this->get(URL::signedRoute('email.change.verify', ['user' => $user->id, 'token' => $token]))
        ->assertRedirect(Frontend::url(Frontend::EMAIL_CHANGED_PATH, ['status' => 'changed']));

    expect($user->fresh()->email)->toBe('new@example.com');
});

test('a bad email change token lands in the SPA saying so', function () {
    $user = User::factory()->create(['email' => 'old@example.com']);

    PendingEmailChange::query()->create([
        'user_id' => $user->id,
        'email' => 'new@example.com',
        'token' => hash('sha256', 'real-token'),
        'created_at' => now(),
    ]);

    $this->get(URL::signedRoute('email.change.verify', ['user' => $user->id, 'token' => 'wrong-token']))
        ->assertRedirect(Frontend::url(Frontend::EMAIL_CHANGED_PATH, ['status' => 'invalid']));

    expect($user->fresh()->email)->toBe('old@example.com');
});

test('a confirmed password change lands the reader back in the SPA', function () {
    $user = User::factory()->create();
    $token = 'a-token';

    PendingPasswordChange::query()->create([
        'user_id' => $user->id,
        'password' => Hash::make('a-new-password'),
        'token' => hash('sha256', $token),
        'created_at' => now(),
    ]);

    $this->get(URL::temporarySignedRoute('password.change.confirm', now()->addDay(), [
        'user' => $user->id,
        'token' => $token,
    ]))->assertRedirect(Frontend::url(Frontend::PASSWORD_CHANGED_PATH, ['status' => 'changed']));

    expect(Hash::check('a-new-password', $user->fresh()->password))->toBeTrue();
});

test('an expired password change lands in the SPA saying so', function () {
    $user = User::factory()->create();
    $token = 'a-token';

    PendingPasswordChange::query()->create([
        'user_id' => $user->id,
        'password' => Hash::make('a-new-password'),
        'token' => hash('sha256', $token),
        'created_at' => now()->subHours((int) config('battlezones.password_change_token_expiry') + 1),
    ]);

    $this->get(URL::temporarySignedRoute('password.change.confirm', now()->addDay(), [
        'user' => $user->id,
        'token' => $token,
    ]))->assertRedirect(Frontend::url(Frontend::PASSWORD_CHANGED_PATH, ['status' => 'expired']));

    expect(Hash::check('a-new-password', $user->fresh()->password))->toBeFalse();
});
