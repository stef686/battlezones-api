<?php

use App\Enums\PrivacyOption;
use App\Models\User;

test('GET returns defaults when no privacy settings are saved', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('privacy-settings'))
        ->assertSuccessful()
        ->assertJsonPath('data.messaging.value', 'anyone')
        ->assertJsonPath('data.messaging.label', 'Anyone')
        ->assertJsonPath('data.profile.value', 'anyone')
        ->assertJsonPath('data.profile.label', 'Anyone');
});

test('GET reflects saved privacy preferences', function () {
    $user = User::factory()->create([
        'privacy_settings' => [
            'messaging' => 'followers_only',
            'profile' => 'mutual_followers',
        ],
    ]);

    $this->actingAs($user)
        ->getJson(route('privacy-settings'))
        ->assertSuccessful()
        ->assertJsonPath('data.messaging.value', 'followers_only')
        ->assertJsonPath('data.messaging.label', 'Followers Only')
        ->assertJsonPath('data.profile.value', 'mutual_followers')
        ->assertJsonPath('data.profile.label', 'Mutual Followers');
});

test('GET returns all available options', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->getJson(route('privacy-settings'))
        ->assertSuccessful();

    $options = $response->json('data.options');

    expect($options)->toHaveCount(count(PrivacyOption::cases()));

    foreach (PrivacyOption::cases() as $index => $case) {
        expect($options[$index]['value'])->toBe($case->value)
            ->and($options[$index]['label'])->toBe($case->label());
    }
});

test('PATCH updates a single privacy setting', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson(route('privacy-settings.update'), [
            'messaging' => 'followers_only',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.messaging.value', 'followers_only');

    expect($user->fresh()->privacy_settings['messaging'])->toBe('followers_only');
});

test('PATCH updates both privacy settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson(route('privacy-settings.update'), [
            'messaging' => 'followers_only',
            'profile' => 'mutual_followers',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.messaging.value', 'followers_only')
        ->assertJsonPath('data.profile.value', 'mutual_followers');
});

test('PATCH preserves other setting when updating one', function () {
    $user = User::factory()->create([
        'privacy_settings' => [
            'messaging' => 'followers_only',
        ],
    ]);

    $this->actingAs($user)
        ->patchJson(route('privacy-settings.update'), [
            'profile' => 'following_only',
        ])
        ->assertSuccessful();

    $settings = $user->fresh()->privacy_settings;
    expect($settings['messaging'])->toBe('followers_only')
        ->and($settings['profile'])->toBe('following_only');
});

test('PATCH rejects invalid values', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson(route('privacy-settings.update'), [
            'messaging' => 'invalid_option',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('messaging');
});
