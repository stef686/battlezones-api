<?php

use App\Actions\Events\SendEventInvite;
use App\Enums\EventInviteRole;
use App\Enums\EventOrganiserRole;
use App\Enums\NotificationType;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventInvite;
use App\Models\User;
use App\Notifications\Events\EventInviteNotification;
use App\Notifications\Events\ExistingAccountInvitedNotification;
use Illuminate\Support\Facades\Notification;

function eventWithLead(): array
{
    $event = Event::factory()->published()->create();
    $lead = User::factory()->create();
    $event->organisers()->attach($lead, ['role' => EventOrganiserRole::Lead->value]);

    return [$event, $lead];
}

/**
 * Sends an invite and returns the plain token that only the email ever sees.
 * Requires Notification::fake().
 */
function sendInviteAndCaptureToken(Event $event, string $email = 'captain@example.com'): string
{
    app(SendEventInvite::class)->handle($event, $email, EventInviteRole::Captain);

    $token = null;

    Notification::assertSentTo(
        User::where('email', $email)->firstOrFail(),
        function (EventInviteNotification $notification) use (&$token): bool {
            $token = $notification->plainToken;

            return true;
        },
    );

    return $token;
}

test('an organiser invites a captain, creating an unclaimed account', function () {
    Notification::fake();

    [$event, $lead] = eventWithLead();

    $this->actingAs($lead)
        ->postJson(route('events.invites.store', ['event' => $event->slug]), [
            'email' => 'captain@example.com',
            'name' => 'Horus Lupercal',
        ])
        ->assertCreated();

    $invited = User::where('email', 'captain@example.com')->firstOrFail();

    expect($invited->isClaimed())->toBeFalse()
        ->and($invited->password)->toBeNull()
        ->and(EventInvite::where('event_id', $event->id)->where('user_id', $invited->id)->exists())->toBeTrue();

    Notification::assertSentTo($invited, EventInviteNotification::class);
});

test('inviting an email that already has an account links it and tells that person', function () {
    Notification::fake();

    [$event, $lead] = eventWithLead();
    $existing = User::factory()->create(['email' => 'veteran@example.com']);

    $this->actingAs($lead)
        ->postJson(route('events.invites.store', ['event' => $event->slug]), [
            'email' => 'veteran@example.com',
        ])
        ->assertCreated();

    $invite = EventInvite::where('event_id', $event->id)->firstOrFail();

    expect(User::where('email', 'veteran@example.com')->count())->toBe(1)
        ->and($invite->user_id)->toBe($existing->id)
        // A claimed account already has a way in; a second credential emailed
        // to it would bypass the password its owner chose.
        ->and($invite->token)->toBeNull();

    Notification::assertSentTo($existing, ExistingAccountInvitedNotification::class);
    Notification::assertNotSentTo($existing, EventInviteNotification::class);
});

test('an invite token opens the invitation repeatedly', function () {
    Notification::fake();

    [$event] = eventWithLead();
    $token = sendInviteAndCaptureToken($event);

    foreach (range(1, 2) as $ignored) {
        $this->getJson(route('invites.show', ['token' => $token]))
            ->assertSuccessful()
            ->assertJsonPath('data.role', 'captain')
            ->assertJsonPath('data.is_claimed', false)
            ->assertJsonPath('data.event.slug', $event->slug);
    }
});

test('an expired token is distinguishable from a revoked one and from an unknown one', function () {
    Notification::fake();

    [$event] = eventWithLead();

    $expiredToken = sendInviteAndCaptureToken($event, 'expired@example.com');
    EventInvite::findByToken($expiredToken)->forceFill(['expires_at' => now()->subDay()])->save();

    $revokedToken = sendInviteAndCaptureToken($event, 'revoked@example.com');
    EventInvite::findByToken($revokedToken)->revoke();

    $this->getJson(route('invites.show', ['token' => $expiredToken]))
        ->assertStatus(410)
        ->assertJsonPath('code', 'invite_expired');

    $this->getJson(route('invites.show', ['token' => $revokedToken]))
        ->assertStatus(410)
        ->assertJsonPath('code', 'invite_revoked');

    $this->getJson(route('invites.show', ['token' => 'nonsense']))
        ->assertNotFound()
        ->assertJsonPath('code', 'invite_not_found');
});

test('an invite token stays good until two days after the event ends', function () {
    Notification::fake();

    [$event] = eventWithLead();
    $token = sendInviteAndCaptureToken($event);

    $this->travelTo($event->ends_at->copy()->addDays(2)->subMinute());
    $this->getJson(route('invites.show', ['token' => $token]))->assertSuccessful();

    $this->travelTo($event->ends_at->copy()->addDays(2)->addMinute());
    $this->getJson(route('invites.show', ['token' => $token]))
        ->assertStatus(410)
        ->assertJsonPath('code', 'invite_expired');
});

test('an invite token grants access as the unclaimed account it was sent to', function () {
    Notification::fake();

    [$event] = eventWithLead();
    $token = sendInviteAndCaptureToken($event);
    $invited = User::where('email', 'captain@example.com')->firstOrFail();

    $apiToken = $this->postJson(route('invites.session', ['token' => $token]), ['device_name' => 'iPhone'])
        ->assertSuccessful()
        ->json('token');

    $this->withToken($apiToken)
        ->getJson(route('profile'))
        ->assertSuccessful()
        ->assertJsonPath('data.id', $invited->id);
});

test('a revoked invite token grants no access', function () {
    Notification::fake();

    [$event] = eventWithLead();
    $token = sendInviteAndCaptureToken($event);
    EventInvite::findByToken($token)->revoke();

    $this->postJson(route('invites.session', ['token' => $token]), ['device_name' => 'iPhone'])
        ->assertStatus(410)
        ->assertJsonPath('code', 'invite_revoked');
});

test('setting a password from an invitation claims the account', function () {
    Notification::fake();

    [$event] = eventWithLead();
    $token = sendInviteAndCaptureToken($event);

    $this->postJson(route('invites.claim', ['token' => $token]), [
        'password' => 'a-real-password',
        'password_confirmation' => 'a-real-password',
        'device_name' => 'iPhone',
    ])->assertSuccessful();

    $invited = User::where('email', 'captain@example.com')->firstOrFail();

    expect($invited->isClaimed())->toBeTrue();

    $this->postJson(route('login.token'), [
        'email' => 'captain@example.com',
        'password' => 'a-real-password',
        'device_name' => 'iPhone',
    ])->assertSuccessful();
});

test('claiming revokes every outstanding invite the account holds', function () {
    Notification::fake();

    [$event] = eventWithLead();
    $otherEvent = Event::factory()->published()->create();

    $token = sendInviteAndCaptureToken($event);
    app(SendEventInvite::class)->handle($otherEvent, 'captain@example.com', EventInviteRole::Captain);

    $this->postJson(route('invites.claim', ['token' => $token]), [
        'password' => 'a-real-password',
        'password_confirmation' => 'a-real-password',
        'device_name' => 'iPhone',
    ])->assertSuccessful();

    $invited = User::where('email', 'captain@example.com')->firstOrFail();

    expect(EventInvite::where('user_id', $invited->id)->outstanding()->count())->toBe(0);

    $this->getJson(route('invites.show', ['token' => $token]))
        ->assertStatus(410)
        ->assertJsonPath('code', 'invite_revoked');
});

test('only an organiser may invite', function () {
    Notification::fake();

    [$event] = eventWithLead();

    $this->actingAs(User::factory()->create())
        ->postJson(route('events.invites.store', ['event' => $event->slug]), [
            'email' => 'captain@example.com',
        ])
        ->assertForbidden();

    expect(EventInvite::count())->toBe(0);
});

test('the invite email goes out whatever the account has muted', function () {
    Notification::fake();

    [$event, $lead] = eventWithLead();

    $muted = User::factory()->create([
        'email' => 'muted@example.com',
        'notification_settings' => array_fill_keys(
            array_map(fn (NotificationType $type): string => $type->value, NotificationType::cases()),
            [],
        ),
    ]);

    $this->actingAs($lead)
        ->postJson(route('events.invites.store', ['event' => $event->slug]), [
            'email' => 'muted@example.com',
        ])
        ->assertCreated();

    Notification::assertSentTo(
        $muted,
        ExistingAccountInvitedNotification::class,
        fn (object $notification, array $channels): bool => in_array('mail', $channels, true),
    );
});

test('a captain invites a partner into the attendee they already have', function () {
    Notification::fake();

    [$event] = eventWithLead();
    $captain = User::factory()->create();
    $attendee = EventAttendee::factory()->for($event)->create();

    $invite = app(SendEventInvite::class)->handle(
        event: $event,
        email: 'partner@example.com',
        role: EventInviteRole::Player,
        attendee: $attendee,
        invitedBy: $captain,
    );

    expect($invite->role)->toBe(EventInviteRole::Player)
        ->and($invite->event_attendee_id)->toBe($attendee->id)
        ->and($invite->invited_by_id)->toBe($captain->id);

    $token = null;
    Notification::assertSentTo(
        User::where('email', 'partner@example.com')->firstOrFail(),
        function (EventInviteNotification $notification) use (&$token): bool {
            $token = $notification->plainToken;

            return true;
        },
    );

    $this->getJson(route('invites.show', ['token' => $token]))
        ->assertSuccessful()
        ->assertJsonPath('data.role', 'player')
        ->assertJsonPath('data.attendee_id', $attendee->id);
});

test('re-inviting the same person retires the token already sent to them', function () {
    Notification::fake();

    [$event] = eventWithLead();
    $firstToken = sendInviteAndCaptureToken($event);

    app(SendEventInvite::class)->handle($event, 'captain@example.com', EventInviteRole::Captain);

    expect(EventInvite::where('event_id', $event->id)->count())->toBe(1);

    $this->getJson(route('invites.show', ['token' => $firstToken]))
        ->assertNotFound()
        ->assertJsonPath('code', 'invite_not_found');
});
