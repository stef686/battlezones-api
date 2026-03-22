<?php

use App\Enums\MessageType;
use App\Models\Conversation;
use App\Models\User;

test('it renames a group conversation', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $conversation = Conversation::factory()->group('Old Name')->create();
    $conversation->users()->attach([$user->id, $other->id]);

    $response = $this->actingAs($user)
        ->patchJson(route('conversations.name.update', $conversation), [
            'name' => 'New Name',
        ])
        ->assertSuccessful();

    expect($response->json('data.name'))->toBe('New Name');
    $this->assertDatabaseHas('conversations', ['id' => $conversation->id, 'name' => 'New Name']);
});

test('it creates a system message when renaming', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $conversation = Conversation::factory()->group()->create();
    $conversation->users()->attach([$user->id, $other->id]);

    $this->actingAs($user)
        ->patchJson(route('conversations.name.update', $conversation), [
            'name' => 'Cool Group',
        ])
        ->assertSuccessful();

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'type' => MessageType::System->value,
        'body' => "{$user->public_name} renamed the group to Cool Group",
        'user_id' => null,
    ]);
});

test('it rejects non-group conversations', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $other)->create();

    $this->actingAs($user)
        ->patchJson(route('conversations.name.update', $conversation), [
            'name' => 'New Name',
        ])
        ->assertForbidden();
});

test('it rejects non-member users', function () {
    $outsider = User::factory()->create();
    $member = User::factory()->create();

    $conversation = Conversation::factory()->group()->create();
    $conversation->users()->attach($member->id);

    $this->actingAs($outsider)
        ->patchJson(route('conversations.name.update', $conversation), [
            'name' => 'New Name',
        ])
        ->assertForbidden();
});

test('it validates name is required', function () {
    $user = User::factory()->create();

    $conversation = Conversation::factory()->group()->create();
    $conversation->users()->attach($user->id);

    $this->actingAs($user)
        ->patchJson(route('conversations.name.update', $conversation), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});
