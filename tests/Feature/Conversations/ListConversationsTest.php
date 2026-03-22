<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

test('it lists only the user conversations', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $thirdUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $user->id]);

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
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $user->id]);
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
        'user_id' => $user->id,
        'created_at' => now()->subHour(),
    ]);

    $newer = Conversation::factory()->withUsers($user, $userB)->create();
    Message::factory()->create([
        'conversation_id' => $newer->id,
        'user_id' => $user->id,
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
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $user->id, 'body' => 'Hi']);

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
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $user->id]);
    $conversation->users()->updateExistingPivot($user->id, ['archived_at' => now()]);

    $this->actingAs($user)
        ->getJson(route('conversations.index'))
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

test('it lists archived conversations with tab=archived', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $otherUser->id]);
    $conversation->users()->updateExistingPivot($user->id, ['archived_at' => now()]);

    $this->actingAs($user)
        ->getJson(route('conversations.index', ['tab' => 'archived']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('primary tab lists conversations user started', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $user->id]);

    $this->actingAs($user)
        ->getJson(route('conversations.index', ['tab' => 'primary']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('primary tab lists conversations with followed users', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $user->following()->attach($otherUser);

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $otherUser->id]);

    $this->actingAs($user)
        ->getJson(route('conversations.index', ['tab' => 'primary']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('primary tab excludes event conversations', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create(['event_id' => 1]);
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $user->id]);

    $this->actingAs($user)
        ->getJson(route('conversations.index', ['tab' => 'primary']))
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

test('events tab lists only event conversations', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $eventConversation = Conversation::factory()->withUsers($user, $otherUser)->create(['event_id' => 1]);
    Message::factory()->create(['conversation_id' => $eventConversation->id, 'user_id' => $user->id]);

    $normalConversation = Conversation::factory()->withUsers($user, $otherUser)->create();
    Message::factory()->create(['conversation_id' => $normalConversation->id, 'user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->getJson(route('conversations.index', ['tab' => 'events']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');

    expect($response->json('data.0.id'))->toBe($eventConversation->id);
});

test('requests tab lists conversations from unfollowed users who initiated', function () {
    $user = User::factory()->create();
    $stranger = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $stranger)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $stranger->id]);

    $this->actingAs($user)
        ->getJson(route('conversations.index', ['tab' => 'requests']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('requests tab excludes event conversations', function () {
    $user = User::factory()->create();
    $stranger = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $stranger)->create(['event_id' => 1]);
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $stranger->id]);

    $this->actingAs($user)
        ->getJson(route('conversations.index', ['tab' => 'requests']))
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

test('tab validation rejects invalid values', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('conversations.index', ['tab' => 'invalid']))
        ->assertUnprocessable();
});

test('conversation moves from requests to primary when user follows participant', function () {
    $user = User::factory()->create();
    $stranger = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $stranger)->create();
    Message::factory()->create(['conversation_id' => $conversation->id, 'user_id' => $stranger->id]);

    // Conversation starts in requests
    $this->actingAs($user)
        ->getJson(route('conversations.index', ['tab' => 'requests']))
        ->assertJsonCount(1, 'data');

    $this->actingAs($user)
        ->getJson(route('conversations.index', ['tab' => 'primary']))
        ->assertJsonCount(0, 'data');

    // User follows the stranger
    $user->following()->attach($stranger);

    // Now it appears in primary, not requests
    $this->actingAs($user)
        ->getJson(route('conversations.index', ['tab' => 'primary']))
        ->assertJsonCount(1, 'data');

    $this->actingAs($user)
        ->getJson(route('conversations.index', ['tab' => 'requests']))
        ->assertJsonCount(0, 'data');
});

test('it returns correct unread count', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();

    // Auth user initiates (puts conversation in primary tab)
    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'created_at' => now()->subMinutes(10),
    ]);

    // 3 messages from the other user (all unread)
    Message::factory()->count(3)->create([
        'conversation_id' => $conversation->id,
        'user_id' => $otherUser->id,
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('conversations.index'))
        ->assertSuccessful();

    expect($response->json('data.0.unread_count'))->toBe(3);
});

test('it returns correct unread count after reading', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $conversation = Conversation::factory()->withUsers($user, $otherUser)->create();

    // Auth user initiates
    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'created_at' => now()->subHours(3),
    ]);

    // Old message (before last_read_at)
    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $otherUser->id,
        'created_at' => now()->subHours(2),
    ]);

    // Mark as read 1 hour ago
    $conversation->users()->updateExistingPivot($user->id, ['last_read_at' => now()->subHour()]);

    // New message (after last_read_at)
    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $otherUser->id,
        'created_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('conversations.index'))
        ->assertSuccessful();

    expect($response->json('data.0.unread_count'))->toBe(1);
});

test('it paginates results', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 20; $i++) {
        $other = User::factory()->create();
        $conv = Conversation::factory()->withUsers($user, $other)->create();
        Message::factory()->create(['conversation_id' => $conv->id, 'user_id' => $user->id]);
    }

    $this->actingAs($user)
        ->getJson(route('conversations.index'))
        ->assertSuccessful()
        ->assertJsonStructure(['data', 'links', 'meta']);
});
