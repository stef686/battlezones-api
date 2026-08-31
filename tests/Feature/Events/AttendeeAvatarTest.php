<?php

use App\Enums\EventOrganiserRole;
use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\Round;
use App\Models\User;
use App\Services\UploadStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @return array{0: Event, 1: EventAttendee, 2: User}
 */
function teamWithAvatar(): array
{
    $event = Event::factory()->published()->standingsVisible()->create(['attendee_size' => 2]);
    $captain = User::factory()->create();
    $attendee = EventAttendee::factory()->for($event)->withMember($captain)->create(['name' => 'Sons of Terra']);

    return [$event, $attendee, $captain];
}

test('a team uploads an avatar, which is squared off and comes back on the team', function () {
    Storage::fake(UploadStorage::name());

    [$event, $attendee, $captain] = teamWithAvatar();

    $avatar = $this->actingAs($captain)
        ->post(route('events.attendees.avatar.store', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'avatar' => UploadedFile::fake()->image('badge.png', 900, 600),
        ])
        ->assertSuccessful()
        ->json('data.avatar');

    expect($avatar)->toBeString();

    [$width, $height] = getimagesize(UploadStorage::disk()->path((string) $attendee->refresh()->avatar_path));

    expect([$width, $height])->toEqual([256, 256]);
});

test('a second upload replaces the first rather than leaving it on the disk', function () {
    Storage::fake(UploadStorage::name());

    [$event, $attendee, $captain] = teamWithAvatar();

    $upload = fn () => $this->actingAs($captain)
        ->post(route('events.attendees.avatar.store', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'avatar' => UploadedFile::fake()->image('badge.png', 400, 400),
        ])
        ->assertSuccessful();

    $upload();
    $first = (string) $attendee->refresh()->avatar_path;

    $upload();

    expect($attendee->refresh()->avatar_path)->not->toBe($first)
        ->and(UploadStorage::disk()->exists($first))->toBeFalse();
});

test('a team takes its avatar back off, and the file goes with it', function () {
    Storage::fake(UploadStorage::name());

    [$event, $attendee, $captain] = teamWithAvatar();

    $this->actingAs($captain)
        ->post(route('events.attendees.avatar.store', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'avatar' => UploadedFile::fake()->image('badge.png', 400, 400),
        ])
        ->assertSuccessful();

    $stored = (string) $attendee->refresh()->avatar_path;

    $this->actingAs($captain)
        ->deleteJson(route('events.attendees.avatar.destroy', ['event' => $event->slug, 'attendee' => $attendee->id]))
        ->assertSuccessful()
        ->assertJsonPath('data.avatar', null);

    expect($attendee->refresh()->avatar_path)->toBeNull()
        ->and(UploadStorage::disk()->exists($stored))->toBeFalse();
});

test('somebody else\'s team is not theirs to badge', function () {
    Storage::fake(UploadStorage::name());

    [$event, $attendee] = teamWithAvatar();

    $this->actingAs(User::factory()->create())
        ->post(route('events.attendees.avatar.store', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'avatar' => UploadedFile::fake()->image('badge.png', 400, 400),
        ])
        ->assertForbidden();

    expect($attendee->refresh()->avatar_path)->toBeNull();
});

test('an organiser badges a team that cannot manage it themselves', function () {
    Storage::fake(UploadStorage::name());

    [$event, $attendee] = teamWithAvatar();
    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    $this->actingAs($organiser)
        ->post(route('events.attendees.avatar.store', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'avatar' => UploadedFile::fake()->image('badge.png', 400, 400),
        ])
        ->assertSuccessful();

    expect($attendee->refresh()->avatar_path)->not->toBeNull();
});

test('a scriptable document is not an image, and a tiny one is not upscaled', function () {
    Storage::fake(UploadStorage::name());

    [$event, $attendee, $captain] = teamWithAvatar();

    $this->actingAs($captain)
        ->post(route('events.attendees.avatar.store', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'avatar' => UploadedFile::fake()->create('badge.svg', 40, 'image/svg+xml'),
        ])
        ->assertUnprocessable();

    $this->actingAs($captain)
        ->post(route('events.attendees.avatar.store', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'avatar' => UploadedFile::fake()->image('badge.png', 64, 64),
        ])
        ->assertUnprocessable();

    expect($attendee->refresh()->avatar_path)->toBeNull();
});

test('the avatar rides along wherever a team is listed', function () {
    Storage::fake(UploadStorage::name());

    [$event, $attendee, $captain] = teamWithAvatar();
    $event->forceFill(['status' => EventStatus::Active])->save();
    $opponent = EventAttendee::factory()->for($event)->withMember(User::factory()->create())->create();

    $this->actingAs($captain)
        ->post(route('events.attendees.avatar.store', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'avatar' => UploadedFile::fake()->image('badge.png', 400, 400),
        ])
        ->assertSuccessful();

    $round = Round::factory()->for($event)->live()->create(['number' => 1]);
    $game = $round->games()->create(['table_number' => 1]);
    $game->attendees()->attach([$attendee->id, $opponent->id]);

    // The listing, the pairings and the standings all draw a team as a row,
    // and the badge is what tells two of them apart at a glance.
    $listed = $this->getJson(route('events.attendees.index', ['event' => $event->slug]))->assertSuccessful();
    expect(collect($listed->json('data'))->firstWhere('id', $attendee->id)['avatar'])->toBeString()
        ->and(collect($listed->json('data'))->firstWhere('id', $opponent->id)['avatar'])->toBeNull();

    $paired = $this->getJson(route('events.rounds.show', ['event' => $event->slug, 'round' => $round->id]))
        ->assertSuccessful()
        ->json('data.games.0.attendees');

    expect(collect($paired)->firstWhere('id', $attendee->id)['avatar'])->toBeString();

    $standings = $this->getJson(route('events.standings.index', ['event' => $event->slug]))->assertSuccessful();

    expect(collect($standings->json('data'))->firstWhere('id', $attendee->id)['attendee']['avatar'])->toBeString();
});
