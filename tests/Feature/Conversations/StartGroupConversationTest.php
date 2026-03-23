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
        ->postJson(route('conversations.store'), [
            'name' => 'My Group',
            'recipient_ids' => $members->pluck('id')->all(),
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
        ->postJson(route('conversations.store'), [
            'name' => 'Group',
            'recipient_ids' => $members->pluck('id')->all(),
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
        ->postJson(route('conversations.store'), [
            'name' => 'Group',
            'recipient_ids' => $members->pluck('id')->all(),
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
        ->postJson(route('conversations.store'), [
            'name' => 'Group',
            'recipient_ids' => $members->pluck('id')->all(),
            'body' => 'Hey!',
        ])
        ->assertSuccessful();

    foreach ($members as $member) {
        Notification::assertSentTo($member, NewMessageNotification::class);
    }

    Notification::assertNotSentTo($sender, NewMessageNotification::class);
});

test('it creates a direct conversation when only one recipient id is given', function () {
    Notification::fake();
    $sender = User::factory()->create();
    $member = User::factory()->create();

    $this->actingAs($sender)
        ->postJson(route('conversations.store'), [
            'name' => 'Group',
            'recipient_ids' => [$member->id],
            'body' => 'Hey!',
        ])
        ->assertSuccessful();

    $this->assertDatabaseHas('conversations', ['is_group' => false]);
});

test('it allows at most 9 recipient ids', function () {
    $sender = User::factory()->create();
    $members = User::factory()->count(10)->create();

    $this->actingAs($sender)
        ->postJson(route('conversations.store'), [
            'name' => 'Group',
            'recipient_ids' => $members->pluck('id')->all(),
            'body' => 'Hey!',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('recipient_ids');
});

test('it rejects invalid recipient ids', function () {
    $sender = User::factory()->create();

    $this->actingAs($sender)
        ->postJson(route('conversations.store'), [
            'name' => 'Group',
            'recipient_ids' => [99998, 99999],
            'body' => 'Hey!',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('recipient_ids.0');
});

test('it rejects self in recipient ids', function () {
    $sender = User::factory()->create();
    $member = User::factory()->create();

    $this->actingAs($sender)
        ->postJson(route('conversations.store'), [
            'name' => 'Group',
            'recipient_ids' => [$sender->id, $member->id],
            'body' => 'Hey!',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('recipient_ids');
});

test('it requires name for group conversations', function () {
    $sender = User::factory()->create();
    $members = User::factory()->count(2)->create();

    $this->actingAs($sender)
        ->postJson(route('conversations.store'), [
            'recipient_ids' => $members->pluck('id')->all(),
            'body' => 'Hey!',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

test('it validates required fields', function () {
    $sender = User::factory()->create();

    $this->actingAs($sender)
        ->postJson(route('conversations.store'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['recipient_ids', 'body']);
});
