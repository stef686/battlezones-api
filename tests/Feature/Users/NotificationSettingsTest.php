<?php

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Models\User;

test('it returns all notification types with defaults when none are saved', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->getJson(route('notification-settings'))
        ->assertSuccessful();

    foreach (NotificationType::cases() as $type) {
        $response->assertJsonPath("data.{$type->value}.label", $type->label())
            ->assertJsonPath("data.{$type->value}.channels", ['email']);
    }
});

test('it reflects saved notification preferences', function () {
    $user = User::factory()->create([
        'notification_settings' => [
            'primary_messages' => ['email', 'push'],
            'message_requests' => [],
        ],
    ]);

    $this->actingAs($user)
        ->getJson(route('notification-settings'))
        ->assertSuccessful()
        ->assertJsonPath('data.primary_messages.channels', ['email', 'push'])
        ->assertJsonPath('data.message_requests.channels', [])
        ->assertJsonPath('data.event_messages.channels', ['email'])
        ->assertJsonPath('data.round_live.channels', ['email']);
});

test('it can update a single notification setting', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson(route('notification-settings.update'), [
            'primary_messages' => ['push'],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.primary_messages.channels', ['push']);

    expect($user->fresh()->notification_settings['primary_messages'])->toBe(['push']);
});

test('it can update multiple notification settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson(route('notification-settings.update'), [
            'primary_messages' => ['push'],
            'event_messages' => ['email', 'push'],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.primary_messages.channels', ['push'])
        ->assertJsonPath('data.event_messages.channels', ['email', 'push']);
});

test('it can set both channels at once', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson(route('notification-settings.update'), [
            'round_live' => ['email', 'push'],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.round_live.channels', ['email', 'push']);
});

test('it can set empty array to disable notifications', function () {
    $user = User::factory()->create([
        'notification_settings' => ['primary_messages' => ['email']],
    ]);

    $this->actingAs($user)
        ->patchJson(route('notification-settings.update'), [
            'primary_messages' => [],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.primary_messages.channels', []);

    expect($user->fresh()->notification_settings['primary_messages'])->toBe([]);
});

test('update is idempotent', function () {
    $user = User::factory()->create();

    $payload = ['primary_messages' => ['push']];

    $first = $this->actingAs($user)
        ->patchJson(route('notification-settings.update'), $payload)
        ->assertSuccessful()
        ->json();

    $second = $this->actingAs($user)
        ->patchJson(route('notification-settings.update'), $payload)
        ->assertSuccessful()
        ->json();

    expect($first)->toBe($second);
});

test('it rejects invalid channel values', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson(route('notification-settings.update'), [
            'primary_messages' => ['sms'],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('primary_messages.0');
});

test('it rejects duplicate channels', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson(route('notification-settings.update'), [
            'primary_messages' => ['email', 'email'],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('primary_messages.0');
});

test('update preserves other settings when updating one', function () {
    $user = User::factory()->create([
        'notification_settings' => [
            'primary_messages' => ['email', 'push'],
        ],
    ]);

    $this->actingAs($user)
        ->patchJson(route('notification-settings.update'), [
            'event_messages' => ['push'],
        ])
        ->assertSuccessful();

    $settings = $user->fresh()->notification_settings;
    expect($settings['primary_messages'])->toBe(['email', 'push'])
        ->and($settings['event_messages'])->toBe(['push']);
});

test('getNotificationChannels returns default when no settings saved', function () {
    $user = User::factory()->create();

    $channels = $user->getNotificationChannels(NotificationType::PrimaryMessages);

    expect($channels)->toBe([NotificationChannel::Email]);
});

test('getNotificationChannels returns empty array when set to none', function () {
    $user = User::factory()->create([
        'notification_settings' => ['primary_messages' => []],
    ]);

    $channels = $user->getNotificationChannels(NotificationType::PrimaryMessages);

    expect($channels)->toBe([]);
});
