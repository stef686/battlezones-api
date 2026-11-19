<?php

use App\Actions\Events\DeleteAttendee;
use App\Enums\EventInviteRole;
use App\Enums\EventOrganiserRole;
use App\Enums\EventStatus;
use App\Enums\NotificationType;
use App\Enums\RoundStatus;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventAttendeeMembership;
use App\Models\EventCustomFieldResponse;
use App\Models\EventInvite;
use App\Models\EventPoll;
use App\Models\EventVote;
use App\Models\Game;
use App\Models\Round;
use App\Models\User;
use App\Notifications\Events\RemovedFromEventNotification;
use App\Services\Frontend;
use App\Services\UploadStorage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @return array{0: Event, 1: EventAttendee}
 */
function neverPairedTeam(): array
{
    $event = Event::factory()->published()->create(['attendee_size' => 2]);
    $attendee = EventAttendee::factory()->for($event)->withMember(User::factory())->create(['name' => 'Ada and Grace']);

    return [$event, $attendee];
}

function deleteAttendeeRoute(Event $event, EventAttendee $attendee): string
{
    return route('events.attendees.destroy', ['event' => $event->slug, 'attendee' => $attendee->id]);
}

test('an organiser deletes a team that has never been paired', function () {
    [$event, $attendee] = neverPairedTeam();

    $this->actingAs(coOrganiserOf($event))
        ->deleteJson(deleteAttendeeRoute($event, $attendee))
        ->assertNoContent();

    expect(EventAttendee::find($attendee->id))->toBeNull();
});

test('a platform admin deletes a team too, so the panel keeps working', function () {
    [$event, $attendee] = neverPairedTeam();

    $this->actingAs(User::factory()->admin()->create())
        ->deleteJson(deleteAttendeeRoute($event, $attendee))
        ->assertNoContent();

    expect(EventAttendee::find($attendee->id))->toBeNull();
});

test('a team cannot delete itself, and nor can another player', function (Closure $reader) {
    [$event, $attendee] = neverPairedTeam();

    $this->actingAs($reader($event, $attendee))
        ->deleteJson(deleteAttendeeRoute($event, $attendee))
        ->assertForbidden();

    expect(EventAttendee::find($attendee->id))->not->toBeNull();
})->with([
    'its own player' => [fn (Event $event, EventAttendee $attendee): User => $attendee->members()->firstOrFail()],
    'another player' => [fn (Event $event): User => playerOf($event)],
]);

test('an event only its organisers can see answers 404 to anyone else', function (EventStatus $status) {
    [$event, $attendee] = neverPairedTeam();
    $event->update(['status' => $status]);

    $this->actingAs(playerOf($event))
        ->deleteJson(deleteAttendeeRoute($event, $attendee))
        ->assertNotFound();

    $this->actingAs(coOrganiserOf($event))
        ->deleteJson(deleteAttendeeRoute($event, $attendee))
        ->assertNoContent();
})->with('hidden event statuses');

test('a team that has been paired is refused, pointing at drop', function (RoundStatus $status) {
    [$event, $attendee] = neverPairedTeam();
    $round = Round::factory()->for($event)->create(['number' => 1, 'status' => $status]);
    Game::factory()->for($round)->create()->attendees()->attach($attendee);

    $this->actingAs(coOrganiserOf($event))
        ->deleteJson(deleteAttendeeRoute($event, $attendee))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['attendee' => 'Drop it instead']);

    expect(EventAttendee::find($attendee->id))->not->toBeNull();
})->with([
    'in a live round' => [RoundStatus::Live],
    'in a draft round' => [RoundStatus::Draft],
]);

test('everything belonging to the team goes with it, but its players keep their accounts', function () {
    [$event, $attendee] = neverPairedTeam();
    $player = $attendee->members()->firstOrFail();
    $invited = User::factory()->unclaimed()->create();
    $attendee->members()->attach($invited, ['event_id' => $event->id]);

    $invite = EventInvite::create([
        'event_id' => $event->id,
        'user_id' => $invited->id,
        'event_attendee_id' => $attendee->id,
        'invited_by_id' => $player->id,
        'role' => EventInviteRole::Player,
        'token' => Str::random(64),
        'expires_at' => now()->addWeek(),
    ]);
    $response = EventCustomFieldResponse::factory()->for($attendee, 'attendee')->create();
    $ballot = EventVote::factory()->create([
        'event_poll_id' => EventPoll::factory()->for($event),
        'subject_event_attendee_id' => $attendee->id,
    ]);

    $this->actingAs(coOrganiserOf($event))
        ->deleteJson(deleteAttendeeRoute($event, $attendee))
        ->assertNoContent();

    expect(EventAttendeeMembership::where('event_attendee_id', $attendee->id)->exists())->toBeFalse()
        ->and(EventInvite::find($invite->id))->toBeNull()
        ->and(EventCustomFieldResponse::find($response->id))->toBeNull()
        ->and(EventVote::find($ballot->id))->toBeNull()
        ->and(User::find($player->id))->not->toBeNull()
        ->and(User::find($invited->id))->not->toBeNull();
});

test('the team avatar file is removed from storage once the delete commits', function () {
    Storage::fake(UploadStorage::name());

    [$event, $attendee] = neverPairedTeam();
    $path = "avatars/{$attendee->id}/badge.webp";
    UploadStorage::disk()->put($path, 'badge');
    $attendee->forceFill(['avatar_path' => $path])->save();

    $this->actingAs(coOrganiserOf($event))
        ->deleteJson(deleteAttendeeRoute($event, $attendee))
        ->assertNoContent();

    expect(UploadStorage::disk()->exists($path))->toBeFalse();
});

test('every player in a deleted team is told, by email whether or not they have claimed their account', function () {
    Notification::fake();

    $event = Event::factory()->published()->create(['attendee_size' => 2]);
    $claimed = User::factory()->create();
    $invited = User::factory()->unclaimed()->create();
    $attendee = EventAttendee::factory()->for($event)->withMember($claimed)->create(['name' => 'Ada and Grace']);
    $attendee->members()->attach($invited, ['event_id' => $event->id]);

    $this->actingAs(coOrganiserOf($event))
        ->deleteJson(deleteAttendeeRoute($event, $attendee))
        ->assertNoContent();

    Notification::assertSentTo($claimed, RemovedFromEventNotification::class, fn (RemovedFromEventNotification $notification, array $channels): bool => $channels === ['mail', 'database']);
    Notification::assertSentTo($invited, RemovedFromEventNotification::class, fn (RemovedFromEventNotification $notification, array $channels): bool => $channels === ['mail']);
});

test('an organiser deleting a team they play in is not told about their own delete', function () {
    Notification::fake();

    $event = Event::factory()->published()->create(['attendee_size' => 2]);
    $organiser = organiserOf($event);
    $partner = User::factory()->create();
    $attendee = EventAttendee::factory()->for($event)->withMember($organiser)->create();
    $attendee->members()->attach($partner, ['event_id' => $event->id]);

    $this->actingAs($organiser)
        ->deleteJson(deleteAttendeeRoute($event, $attendee))
        ->assertNoContent();

    Notification::assertNotSentTo($organiser, RemovedFromEventNotification::class);
    Notification::assertSentTo($partner, RemovedFromEventNotification::class);
});

test('players are told only while the event is open or under way', function (EventStatus $status, bool $told) {
    Notification::fake();

    [$event, $attendee] = neverPairedTeam();
    $event->update(['status' => $status]);
    $player = $attendee->members()->firstOrFail();

    $this->actingAs(coOrganiserOf($event))
        ->deleteJson(deleteAttendeeRoute($event, $attendee))
        ->assertNoContent();

    $told
        ? Notification::assertSentTo($player, RemovedFromEventNotification::class)
        : Notification::assertNothingSent();
})->with([
    'published' => [EventStatus::Published, true],
    'active' => [EventStatus::Active, true],
    'completed' => [EventStatus::Completed, false],
    'cancelled' => [EventStatus::Cancelled, false],
]);

test('nobody is told when the delete rolls back', function () {
    Notification::fake();

    [$event, $attendee] = neverPairedTeam();
    $organiser = coOrganiserOf($event);

    try {
        DB::transaction(function () use ($attendee, $organiser): void {
            app(DeleteAttendee::class)->execute($attendee, $organiser);

            throw new RuntimeException('Something after the delete failed.');
        });
    } catch (RuntimeException) {
    }

    expect(EventAttendee::find($attendee->id))->not->toBeNull();

    Notification::assertNothingSent();
});

test('nobody is told when the delete is refused', function () {
    Notification::fake();

    [$event, $attendee] = neverPairedTeam();
    $round = Round::factory()->for($event)->create(['number' => 1]);
    Game::factory()->for($round)->create()->attendees()->attach($attendee);

    $this->actingAs(coOrganiserOf($event))
        ->deleteJson(deleteAttendeeRoute($event, $attendee))
        ->assertUnprocessable();

    expect(EventAttendee::find($attendee->id))->not->toBeNull();

    Notification::assertNothingSent();
});

test('the email names who removed the team and links to the event, and the in-app entry carries only ids', function () {
    Notification::fake();

    $event = Event::factory()->published()->create(['name' => 'London Grand Tournament', 'attendee_size' => 2]);
    $organiser = User::factory()->create(['name' => 'Horus Lupercal', 'show_public_name' => true]);
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Organiser->value]);
    $player = User::factory()->create();
    $attendee = EventAttendee::factory()->for($event)->withMember($player)->create(['name' => 'Ada and Grace']);

    $this->actingAs($organiser)
        ->deleteJson(deleteAttendeeRoute($event, $attendee))
        ->assertNoContent();

    Notification::assertSentTo($player, function (RemovedFromEventNotification $notification) use ($player, $event, $organiser): bool {
        $mail = $notification->toMail($player);

        return $mail->subject === "You're no longer entered in London Grand Tournament"
            && $mail->introLines === ["Horus Lupercal has removed Ada and Grace from London Grand Tournament. If you weren't expecting this, contact the organisers."]
            && $mail->actionText === 'View the event'
            && $mail->actionUrl === Frontend::url('/events/'.$event->slug)
            && $notification->toArray($player) === [
                'type' => 'removed_from_event',
                'event_id' => $event->id,
                'actor_id' => $organiser->id,
            ];
    });

    expect(NotificationType::tryFrom('removed_from_event'))->toBeNull();
});

test('a platform admin is not named to the players, since they do not know them', function () {
    Notification::fake();

    $event = Event::factory()->published()->create(['name' => 'London Grand Tournament', 'attendee_size' => 2]);
    $admin = User::factory()->admin()->create(['name' => 'Malcador']);
    $player = User::factory()->create();
    $attendee = EventAttendee::factory()->for($event)->withMember($player)->create(['name' => 'Ada and Grace']);

    $this->actingAs($admin)
        ->deleteJson(deleteAttendeeRoute($event, $attendee))
        ->assertNoContent();

    Notification::assertSentTo($player, fn (RemovedFromEventNotification $notification): bool => $notification->toMail($player)->introLines
        === ["An organiser has removed Ada and Grace from London Grand Tournament. If you weren't expecting this, contact the organisers."]);
});
