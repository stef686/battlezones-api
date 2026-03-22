<?php

use App\Enums\MessageType;
use App\Models\Conversation;
use App\Models\User;

test('it adds members to a group conversation', function () {
    $sender = User::factory()->create();
    $existing = User::factory()->create();
    $newMember = User::factory()->create();

    $conversation = Conversation::factory()->group()->create();
    $conversation->users()->attach([$sender->id, $existing->id]);

    $this->actingAs($sender)
        ->postJson(route('conversations.members.store', $conversation), [
            'usernames' => [$newMember->username],
        ])
        ->assertSuccessful();

    $this->assertDatabaseHas('conversation_user', [
        'conversation_id' => $conversation->id,
        'user_id' => $newMember->id,
    ]);
});

test('it creates a system message when adding members', function () {
    $sender = User::factory()->create();
    $existing = User::factory()->create();
    $newMember = User::factory()->create();

    $conversation = Conversation::factory()->group()->create();
    $conversation->users()->attach([$sender->id, $existing->id]);

    $this->actingAs($sender)
        ->postJson(route('conversations.members.store', $conversation), [
            'usernames' => [$newMember->username],
        ])
        ->assertSuccessful();

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'type' => MessageType::System->value,
        'body' => "{$sender->public_name} added {$newMember->public_name}",
        'user_id' => null,
    ]);
});

test('it sets visible_from when include_history is false', function () {
    $sender = User::factory()->create();
    $existing = User::factory()->create();
    $newMember = User::factory()->create();

    $conversation = Conversation::factory()->group()->create();
    $conversation->users()->attach([$sender->id, $existing->id]);

    $this->actingAs($sender)
        ->postJson(route('conversations.members.store', $conversation), [
            'usernames' => [$newMember->username],
            'include_history' => false,
        ])
        ->assertSuccessful();

    $pivot = $conversation->users()->where('user_id', $newMember->id)->first()->pivot;
    expect($pivot->visible_from)->not->toBeNull();
});

test('it does not set visible_from when include_history is true', function () {
    $sender = User::factory()->create();
    $existing = User::factory()->create();
    $newMember = User::factory()->create();

    $conversation = Conversation::factory()->group()->create();
    $conversation->users()->attach([$sender->id, $existing->id]);

    $this->actingAs($sender)
        ->postJson(route('conversations.members.store', $conversation), [
            'usernames' => [$newMember->username],
            'include_history' => true,
        ])
        ->assertSuccessful();

    $pivot = $conversation->users()->where('user_id', $newMember->id)->first()->pivot;
    expect($pivot->visible_from)->toBeNull();
});

test('it rejects non-group conversations', function () {
    $sender = User::factory()->create();
    $other = User::factory()->create();
    $newMember = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($sender, $other)->create();

    $this->actingAs($sender)
        ->postJson(route('conversations.members.store', $conversation), [
            'usernames' => [$newMember->username],
        ])
        ->assertForbidden();
});

test('it rejects non-member users', function () {
    $outsider = User::factory()->create();
    $member = User::factory()->create();
    $newMember = User::factory()->create();

    $conversation = Conversation::factory()->group()->create();
    $conversation->users()->attach($member->id);

    $this->actingAs($outsider)
        ->postJson(route('conversations.members.store', $conversation), [
            'usernames' => [$newMember->username],
        ])
        ->assertForbidden();
});

test('it rejects already-present usernames', function () {
    $sender = User::factory()->create();
    $existing = User::factory()->create();

    $conversation = Conversation::factory()->group()->create();
    $conversation->users()->attach([$sender->id, $existing->id]);

    $this->actingAs($sender)
        ->postJson(route('conversations.members.store', $conversation), [
            'usernames' => [$existing->username],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('usernames');
});

test('it enforces 10-member cap', function () {
    $sender = User::factory()->create();
    $existingMembers = User::factory()->count(9)->create();

    $conversation = Conversation::factory()->group()->create();
    $conversation->users()->attach($existingMembers->pluck('id')->push($sender->id));

    $newMember = User::factory()->create();

    $this->actingAs($sender)
        ->postJson(route('conversations.members.store', $conversation), [
            'usernames' => [$newMember->username],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('usernames');
});
