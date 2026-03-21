<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

test('it lists only the user conversations', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $thirdUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $otherUser->id]);

    $otherConversation = Conversation::factory()->withUsers($otherUser, $thirdUser)->create();
    Message::factory()->create(['conversation_id' => $otherConversation->id, 'user_id' => $thirdUser->id]);

    $this->actingAs($user)
        ->getJson(route('conversations.index'))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('it excludes deleted conversations', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $otherUser->id]);
    $conversation->users()->updateExistingPivot($user->id, ['deleted_at' => now()]);

    $this->actingAs($user)
        ->getJson(route('conversations.index'))
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

test('it orders by latest message', function () {
    $user = User::factory()->create();
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $older = Conversation::factory()->withUsers($user, $userA)->create();
    Message::factory()->create([
        'conversation_id' => $older->id,
        'user_id' => $userA->id,
        'created_at' => now()->subHour(),
    ]);

    $newer = Conversation::factory()->withUsers($user, $userB)->create();
    Message::factory()->create([
        'conversation_id' => $newer->id,
        'user_id' => $userB->id,
        'created_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('conversations.index'))
        ->assertSuccessful();

    $data = $response->json('data');
    expect($data[0]['id'])->toBe($newer->id)
        ->and($data[1]['id'])->toBe($older->id);
});

test('it includes participant and latest message', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $otherUser->id, 'body' => 'Hi']);

    $response = $this->actingAs($user)
        ->getJson(route('conversations.index'))
        ->assertSuccessful();

    $data = $response->json('data.0');
    expect($data['participant']['id'])->toBe($otherUser->id)
        ->and($data['latest_message']['body'])->toBe('Hi');
});

test('it excludes archived conversations from default list', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $otherUser->id]);
    $conversation->users()->updateExistingPivot($user->id, ['archived_at' => now()]);

    $this->actingAs($user)
        ->getJson(route('conversations.index'))
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

test('it lists archived conversations with filter=archived', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $otherUser->id]);
    $conversation->users()->updateExistingPivot($user->id, ['archived_at' => now()]);

    $this->actingAs($user)
        ->getJson(route('conversations.index', ['filter' => 'archived']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('it paginates results', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 20; $i++) {
        $other = User::factory()->create();
        $conv = Conversation::factory()->withUsers($user, $other)->create();
        Message::factory()->create(['conversation_id' => $conv->id, 'user_id' => $other->id]);
    }

    $this->actingAs($user)
        ->getJson(route('conversations.index'))
        ->assertSuccessful()
        ->assertJsonStructure(['data', 'links', 'meta']);
});
