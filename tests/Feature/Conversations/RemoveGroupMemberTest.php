<?php

use App\Enums\MessageType;
use App\Models\Conversation;
use App\Models\User;

test('it allows a member to remove another member', function () {
    $sender = User::factory()->create();
    $target = User::factory()->create();

    $conversation = Conversation::factory()->group()->create();
    $conversation->users()->attach([$sender->id, $target->id]);

    $this->actingAs($sender)
        ->deleteJson(route('conversations.members.destroy', [$conversation, $target]))
        ->assertNoContent();

    $pivot = $conversation->users()->where('user_id', $target->id)->first()->pivot;
    expect($pivot->deleted_at)->not->toBeNull();
});

test('it creates a system message when removing a member', function () {
    $sender = User::factory()->create();
    $target = User::factory()->create();

    $conversation = Conversation::factory()->group()->create();
    $conversation->users()->attach([$sender->id, $target->id]);

    $this->actingAs($sender)
        ->deleteJson(route('conversations.members.destroy', [$conversation, $target]))
        ->assertNoContent();

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'type' => MessageType::System->value,
        'body' => "{$sender->public_name} removed {$target->public_name}",
        'user_id' => null,
    ]);
});

test('it rejects non-group conversations', function () {
    $sender = User::factory()->create();
    $target = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($sender, $target)->create();

    $this->actingAs($sender)
        ->deleteJson(route('conversations.members.destroy', [$conversation, $target]))
        ->assertForbidden();
});

test('it rejects non-member users', function () {
    $outsider = User::factory()->create();
    $member = User::factory()->create();
    $target = User::factory()->create();

    $conversation = Conversation::factory()->group()->create();
    $conversation->users()->attach([$member->id, $target->id]);

    $this->actingAs($outsider)
        ->deleteJson(route('conversations.members.destroy', [$conversation, $target]))
        ->assertForbidden();
});

test('it rejects removing yourself', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $conversation = Conversation::factory()->group()->create();
    $conversation->users()->attach([$user->id, $other->id]);

    $this->actingAs($user)
        ->deleteJson(route('conversations.members.destroy', [$conversation, $user]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('user');
});

test('it rejects removing a user not in the conversation', function () {
    $sender = User::factory()->create();
    $nonMember = User::factory()->create();

    $conversation = Conversation::factory()->group()->create();
    $conversation->users()->attach($sender->id);

    $this->actingAs($sender)
        ->deleteJson(route('conversations.members.destroy', [$conversation, $nonMember]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('user');
});
