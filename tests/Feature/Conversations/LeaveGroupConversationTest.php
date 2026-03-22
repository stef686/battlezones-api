<?php

use App\Enums\MessageType;
use App\Models\Conversation;
use App\Models\User;

test('it allows a member to leave a group', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $conversation = Conversation::factory()->group()->create();
    $conversation->users()->attach([$user->id, $other->id]);

    $this->actingAs($user)
        ->postJson(route('conversations.leave', $conversation))
        ->assertNoContent();

    $pivot = $conversation->users()->where('user_id', $user->id)->first()->pivot;
    expect($pivot->deleted_at)->not->toBeNull();
});

test('it creates a system message when leaving', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $conversation = Conversation::factory()->group()->create();
    $conversation->users()->attach([$user->id, $other->id]);

    $this->actingAs($user)
        ->postJson(route('conversations.leave', $conversation))
        ->assertNoContent();

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'type' => MessageType::System->value,
        'body' => "{$user->public_name} left the group",
        'user_id' => null,
    ]);
});

test('it rejects non-group conversations', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $other)->create();

    $this->actingAs($user)
        ->postJson(route('conversations.leave', $conversation))
        ->assertForbidden();
});

test('it rejects non-member users', function () {
    $outsider = User::factory()->create();
    $member = User::factory()->create();

    $conversation = Conversation::factory()->group()->create();
    $conversation->users()->attach($member->id);

    $this->actingAs($outsider)
        ->postJson(route('conversations.leave', $conversation))
        ->assertForbidden();
});
