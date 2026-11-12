<?php

use App\Enums\EventInviteRole;
use App\Enums\EventStatus;
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
use App\Services\UploadStorage;
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
