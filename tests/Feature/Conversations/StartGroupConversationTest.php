<?php

use App\Enums\MessageType;
use App\Models\User;
use App\Notifications\Conversations\NewMessageNotification;
use Illuminate\Support\Facades\Notification;

test('it creates a group conversation and returns resource', function () {
    Notification::fake();
    $sender = User::factory()->create();
    $members = User::factory()->count(2)->create();

    $response = $this->actingAs($sender)
        ->postJson(route('conversations.group.store'), [
            'name' => 'My Group',
            'usernames' => $members->pluck('username')->all(),
            'body' => 'Welcome everyone!',
        ])
        ->assertSuccessful();

    $data = $response->json('data');

    expect($data['is_group'])->toBeTrue()
        ->and($data['name'])->toBe('My Group')
        ->and($data['participants'])->toHaveCount(2);

    $this->assertDatabaseCount('conversations', 1);
    $this->assertDatabaseHas('conversations', ['is_group' => true, 'name' => 'My Group']);
});

test('it attaches all members plus the sender', function () {
    Notification::fake();
    $sender = User::factory()->create();
    $members = User::factory()->count(3)->create();

    $this->actingAs($sender)
        ->postJson(route('conversations.group.store'), [
            'name' => 'Group',
            'usernames' => $members->pluck('username')->all(),
            'body' => 'Hey!',
        ])
        ->assertSuccessful();

    $this->assertDatabaseCount('conversation_user', 4);
});

test('it creates a system message', function () {
    Notification::fake();
    $sender = User::factory()->create();
    $members = User::factory()->count(2)->create();

    $this->actingAs($sender)
        ->postJson(route('conversations.group.store'), [
            'name' => 'Group',
            'usernames' => $members->pluck('username')->all(),
            'body' => 'Hello!',
        ])
        ->assertSuccessful();

    $this->assertDatabaseHas('messages', [
        'type' => MessageType::System->value,
        'body' => "{$sender->public_name} created the group",
        'user_id' => null,
    ]);
});

test('it notifies all other members', function () {
    Notification::fake();
    $sender = User::factory()->create();
    $members = User::factory()->count(3)->create();

    $this->actingAs($sender)
        ->postJson(route('conversations.group.store'), [
            'name' => 'Group',
            'usernames' => $members->pluck('username')->all(),
            'body' => 'Hey!',
        ])
        ->assertSuccessful();

    foreach ($members as $member) {
        Notification::assertSentTo($member, NewMessageNotification::class);
    }

    Notification::assertNotSentTo($sender, NewMessageNotification::class);
});

test('it requires at least 2 usernames', function () {
    $sender = User::factory()->create();
    $member = User::factory()->create();

    $this->actingAs($sender)
        ->postJson(route('conversations.group.store'), [
            'name' => 'Group',
            'usernames' => [$member->username],
            'body' => 'Hey!',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('usernames');
});

test('it allows at most 9 usernames', function () {
    $sender = User::factory()->create();
    $members = User::factory()->count(10)->create();

    $this->actingAs($sender)
        ->postJson(route('conversations.group.store'), [
            'name' => 'Group',
            'usernames' => $members->pluck('username')->all(),
            'body' => 'Hey!',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('usernames');
});

test('it rejects invalid usernames', function () {
    $sender = User::factory()->create();

    $this->actingAs($sender)
        ->postJson(route('conversations.group.store'), [
            'name' => 'Group',
            'usernames' => ['nonexistent1', 'nonexistent2'],
            'body' => 'Hey!',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('usernames.0');
});

test('it rejects self in usernames', function () {
    $sender = User::factory()->create();
    $member = User::factory()->create();

    $this->actingAs($sender)
        ->postJson(route('conversations.group.store'), [
            'name' => 'Group',
            'usernames' => [$sender->username, $member->username],
            'body' => 'Hey!',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('usernames');
});

test('it validates required fields', function () {
    $sender = User::factory()->create();

    $this->actingAs($sender)
        ->postJson(route('conversations.group.store'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'usernames', 'body']);
});
