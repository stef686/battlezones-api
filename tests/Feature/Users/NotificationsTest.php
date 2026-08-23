<?php

use App\Models\User;
use Illuminate\Support\Str;

/**
 * A stored in-app notification for this User.
 */
function storeNotification(User $user, string $type = 'result_activity', array $data = []): string
{
    $id = (string) Str::uuid();

    $user->notifications()->create([
        'id' => $id,
        'type' => 'App\\Notifications\\Events\\ResultActivityNotification',
        'data' => ['type' => $type, ...$data],
        'read_at' => null,
    ]);

    return $id;
}

test('a player lists their own notifications, newest first', function () {
    $player = User::factory()->create();
    $other = User::factory()->create();

    $older = storeNotification($player, data: ['game_id' => 1]);
    $this->travel(1)->minutes();
    $newer = storeNotification($player, data: ['game_id' => 2]);
    storeNotification($other);

    $response = $this->actingAs($player)
        ->getJson(route('notifications.index'))
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');

    expect($response->json('data.0.id'))->toBe($newer)
        ->and($response->json('data.1.id'))->toBe($older)
        ->and($response->json('data.0.data.game_id'))->toBe(2)
        ->and($response->json('data.0.read_at'))->toBeNull()
        ->and($response->json('meta.unread_count'))->toBe(2);
});

test('a player marks a notification read', function () {
    $player = User::factory()->create();
    $id = storeNotification($player);

    $this->actingAs($player)
        ->postJson(route('notifications.read', ['notification' => $id]))
        ->assertSuccessful();

    expect($player->notifications()->find($id)->read_at)->not->toBeNull();
});

test('a player cannot mark another player notification read', function () {
    $player = User::factory()->create();
    $other = User::factory()->create();
    $id = storeNotification($other);

    $this->actingAs($player)
        ->postJson(route('notifications.read', ['notification' => $id]))
        ->assertNotFound();

    expect($other->notifications()->find($id)->read_at)->toBeNull();
});

test('a player marks everything read at once', function () {
    $player = User::factory()->create();
    storeNotification($player);
    storeNotification($player);

    $this->actingAs($player)
        ->postJson(route('notifications.read-all'))
        ->assertSuccessful();

    expect($player->unreadNotifications()->count())->toBe(0);
});

test('listing notifications requires authentication', function () {
    $this->getJson(route('notifications.index'))->assertUnauthorized();
});
