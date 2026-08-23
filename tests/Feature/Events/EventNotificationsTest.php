<?php

use App\Enums\EventInviteRole;
use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Enums\ResultActivity;
use App\Jobs\NotifyRoundIsLive;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\User;
use App\Notifications\Events\EventInviteNotification;
use App\Notifications\Events\ResultActivityNotification;
use App\Notifications\Events\RoundLiveNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

test('submitting a result notifies the opponent in app and not the submitter', function () {
    Notification::fake();

    [$event, $game, $mine, $theirs, $player] = submittedGame();

    $opponent = $theirs->memberships()->first()->user;

    Notification::assertSentTo($opponent, ResultActivityNotification::class, function (ResultActivityNotification $notification) use ($opponent, $game): bool {
        return in_array('database', $notification->via($opponent), true)
            && $notification->toArray($opponent)['game_id'] === $game->id;
    });

    Notification::assertNotSentTo($player, ResultActivityNotification::class);
});

test('a preference for email adds a channel rather than replacing the in-app notification', function () {
    $player = User::factory()->create([
        'notification_settings' => [NotificationType::ResultActivity->value => [NotificationChannel::Email->value]],
    ]);

    expect($player->getNotificationDrivers(NotificationType::ResultActivity))->toEqual(['database', 'mail']);
});

test('an empty preference still delivers in app', function () {
    $player = User::factory()->create([
        'notification_settings' => [NotificationType::ResultActivity->value => []],
    ]);

    expect($player->getNotificationDrivers(NotificationType::ResultActivity))->toEqual(['database']);
});

test('correcting a result notifies every player including the original submitter', function () {
    Notification::fake();

    [$event, $game, $mine, $theirs, $player] = submittedGame();

    $opponent = $theirs->memberships()->first()->user;
    $organiser = organiserOf($event);

    $this->actingAs($organiser)
        ->putJson(route('events.games.result.update', ['event' => $event->slug, 'game' => $game->id]), [
            'scores' => [
                $mine->id => ['victory-points' => 70],
                $theirs->id => ['victory-points' => 85],
            ],
        ])
        ->assertSuccessful();

    foreach ([$player, $opponent] as $recipient) {
        Notification::assertSentTo($recipient, ResultActivityNotification::class, fn (ResultActivityNotification $notification): bool => $notification->activity === ResultActivity::Edited
            && $notification->toArray($recipient)['actor_id'] === $organiser->id);
    }
});

test('flagging a result alerts every organiser of the event', function () {
    Notification::fake();

    [$event, $game, , , $player] = submittedGame();

    $lead = organiserOf($event);
    $second = organiserOf($event);

    $this->actingAs($player)
        ->postJson(route('events.games.flag.store', ['event' => $event->slug, 'game' => $game->id]))
        ->assertSuccessful();

    foreach ([$lead, $second] as $organiser) {
        Notification::assertSentTo($organiser, ResultActivityNotification::class, fn (ResultActivityNotification $notification): bool => $notification->activity === ResultActivity::FlagRaised);
    }
});

test('resolving a flag tells the player who raised it', function () {
    Notification::fake();

    [$event, $game, , , $player] = submittedGame();
    $organiser = organiserOf($event);

    $this->actingAs($player)
        ->postJson(route('events.games.flag.store', ['event' => $event->slug, 'game' => $game->id]))
        ->assertSuccessful();

    $this->actingAs($organiser)
        ->postJson(route('events.games.flag.resolve', ['event' => $event->slug, 'game' => $game->id]))
        ->assertSuccessful();

    Notification::assertSentTo($player, ResultActivityNotification::class, fn (ResultActivityNotification $notification): bool => $notification->activity === ResultActivity::FlagResolved
        && $notification->actor->is($organiser));
});

test('publishing a round queues the round-live notifications rather than sending them inline', function () {
    Queue::fake();

    $event = pairableEvent();
    $organiser = organiserOf($event);

    EventAttendee::factory()->count(4)->for($event)->withMember()->create();
    $round = generatePairings($event);

    $this->actingAs($organiser)
        ->postJson(route('events.rounds.publish', ['event' => $event->slug, 'round' => $round->id]))
        ->assertSuccessful();

    Queue::assertPushed(NotifyRoundIsLive::class, fn (NotifyRoundIsLive $job): bool => $job->round->is($round));
});

test('the round-live job notifies every player in the round with their own game', function () {
    Notification::fake();

    $event = pairableEvent();

    EventAttendee::factory()->count(4)->for($event)->withMember()->create();
    $round = generatePairings($event);

    (new NotifyRoundIsLive($round))->handle();

    foreach ($round->games as $game) {
        foreach ($game->players()->get() as $player) {
            Notification::assertSentTo($player, RoundLiveNotification::class, function (RoundLiveNotification $notification) use ($player, $game): bool {
                $payload = $notification->toArray($player);

                return $payload['game_id'] === $game->id
                    && $payload['round_id'] === $game->round_id
                    && in_array('database', $notification->via($player), true);
            });
        }
    }
});

test('transactional email ignores notification preferences', function () {
    $invitee = User::factory()->create(['notification_settings' => [
        NotificationType::EventMessages->value => [],
        NotificationType::ResultActivity->value => [],
    ]]);

    $notification = new EventInviteNotification(Event::factory()->create(), EventInviteRole::Player, 'plain-token');

    expect($notification->via($invitee))->toEqual(['mail']);
});

test('notification settings describe the new event types and their in-app floor', function () {
    $player = User::factory()->create();

    $settings = $this->actingAs($player)
        ->getJson(route('notification-settings'))
        ->assertSuccessful()
        ->json('data');

    expect($settings)->toHaveKeys(['result_activity', 'voting_open', 'round_live'])
        ->and($settings['result_activity']['always_in_app'])->toBeTrue()
        ->and($settings['primary_messages']['always_in_app'])->toBeFalse();
});
