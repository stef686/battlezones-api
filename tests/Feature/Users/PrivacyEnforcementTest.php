<?php

use App\Models\User;
use App\Services\PrivacyService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

// -- Messaging enforcement --

test('messaging is allowed when privacy is set to anyone', function () {
    Notification::fake();
    $sender = User::factory()->create();
    $recipient = User::factory()->create([
        'privacy_settings' => ['messaging' => 'anyone'],
    ]);

    $this->actingAs($sender)
        ->postJson(route('conversations.store'), [
            'recipient_ids' => [$recipient->id],
            'body' => 'Hello!',
        ])
        ->assertSuccessful();
});

test('messaging is blocked when set to followers_only and sender does not follow recipient', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create([
        'privacy_settings' => ['messaging' => 'followers_only'],
    ]);

    $this->actingAs($sender)
        ->postJson(route('conversations.store'), [
            'recipient_ids' => [$recipient->id],
            'body' => 'Hello!',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('recipient_ids');
});

test('messaging is allowed when set to followers_only and sender follows recipient', function () {
    Notification::fake();
    $sender = User::factory()->create();
    $recipient = User::factory()->create([
        'privacy_settings' => ['messaging' => 'followers_only'],
    ]);
    $sender->following()->attach($recipient);

    $this->actingAs($sender)
        ->postJson(route('conversations.store'), [
            'recipient_ids' => [$recipient->id],
            'body' => 'Hello!',
        ])
        ->assertSuccessful();
});

test('messaging is blocked when set to following_only and recipient does not follow sender', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create([
        'privacy_settings' => ['messaging' => 'following_only'],
    ]);

    $this->actingAs($sender)
        ->postJson(route('conversations.store'), [
            'recipient_ids' => [$recipient->id],
            'body' => 'Hello!',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('recipient_ids');
});

test('messaging is allowed when set to following_only and recipient follows sender', function () {
    Notification::fake();
    $sender = User::factory()->create();
    $recipient = User::factory()->create([
        'privacy_settings' => ['messaging' => 'following_only'],
    ]);
    $recipient->following()->attach($sender);

    $this->actingAs($sender)
        ->postJson(route('conversations.store'), [
            'recipient_ids' => [$recipient->id],
            'body' => 'Hello!',
        ])
        ->assertSuccessful();
});

test('messaging is blocked when set to mutual_followers and only one direction exists', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create([
        'privacy_settings' => ['messaging' => 'mutual_followers'],
    ]);
    $sender->following()->attach($recipient);

    $this->actingAs($sender)
        ->postJson(route('conversations.store'), [
            'recipient_ids' => [$recipient->id],
            'body' => 'Hello!',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('recipient_ids');
});

test('messaging is allowed when set to mutual_followers and both directions exist', function () {
    Notification::fake();
    $sender = User::factory()->create();
    $recipient = User::factory()->create([
        'privacy_settings' => ['messaging' => 'mutual_followers'],
    ]);
    $sender->following()->attach($recipient);
    $recipient->following()->attach($sender);

    $this->actingAs($sender)
        ->postJson(route('conversations.store'), [
            'recipient_ids' => [$recipient->id],
            'body' => 'Hello!',
        ])
        ->assertSuccessful();
});

test('messaging is blocked when set to fellow_club_members (stub) and logs warning', function () {
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message) => str_contains($message, 'areClubMembers'));

    $sender = User::factory()->create();
    $recipient = User::factory()->create([
        'privacy_settings' => ['messaging' => 'fellow_club_members'],
    ]);

    $this->actingAs($sender)
        ->postJson(route('conversations.store'), [
            'recipient_ids' => [$recipient->id],
            'body' => 'Hello!',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('recipient_ids');
});

// -- Profile privacy enforcement --

test('profile is accessible when privacy is set to anyone', function () {
    $viewer = User::factory()->create();
    $target = User::factory()->create([
        'privacy_settings' => ['profile' => 'anyone'],
    ]);

    $this->actingAs($viewer)
        ->getJson(route('profile.show', $target))
        ->assertSuccessful();
});

test('profile is blocked when set to followers_only and viewer does not follow target', function () {
    $viewer = User::factory()->create();
    $target = User::factory()->create([
        'privacy_settings' => ['profile' => 'followers_only'],
    ]);

    $this->actingAs($viewer)
        ->getJson(route('profile.show', $target))
        ->assertForbidden();
});

test('profile is accessible when set to followers_only and viewer follows target', function () {
    $viewer = User::factory()->create();
    $target = User::factory()->create([
        'privacy_settings' => ['profile' => 'followers_only'],
    ]);
    $viewer->following()->attach($target);

    $this->actingAs($viewer)
        ->getJson(route('profile.show', $target))
        ->assertSuccessful();
});

test('self-view is always allowed regardless of privacy settings', function () {
    $user = User::factory()->create([
        'privacy_settings' => ['profile' => 'followers_only'],
    ]);

    $this->actingAs($user)
        ->getJson(route('profile.show', $user))
        ->assertSuccessful();
});

test('gallery is blocked when profile privacy denies access', function () {
    $viewer = User::factory()->create();
    $target = User::factory()->create([
        'privacy_settings' => ['profile' => 'followers_only'],
    ]);

    $this->actingAs($viewer)
        ->getJson(route('users.gallery', $target))
        ->assertForbidden();
});

test('followers list is blocked when profile privacy denies access', function () {
    $viewer = User::factory()->create();
    $target = User::factory()->create([
        'privacy_settings' => ['profile' => 'followers_only'],
    ]);

    $this->actingAs($viewer)
        ->getJson(route('users.followers', $target))
        ->assertForbidden();
});

test('following list is blocked when profile privacy denies access', function () {
    $viewer = User::factory()->create();
    $target = User::factory()->create([
        'privacy_settings' => ['profile' => 'followers_only'],
    ]);

    $this->actingAs($viewer)
        ->getJson(route('users.following', $target))
        ->assertForbidden();
});

test('event organiser stub returns false and logs warning', function () {
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message) => str_contains($message, 'isEventOrganiserOf'));

    $organiser = User::factory()->create();
    $participant = User::factory()->create();

    $service = app(PrivacyService::class);

    expect($service->isEventOrganiserOf($organiser, $participant))->toBeFalse();
});
