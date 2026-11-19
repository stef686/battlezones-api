<?php

use App\Actions\Events\SendEventInvite;
use App\Enums\EventInviteRole;
use App\Enums\EventOrganiserRole;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventInvite;
use App\Models\Faction;
use App\Models\User;
use App\Notifications\Events\EventInviteNotification;
use Illuminate\Support\Facades\Notification;

/**
 * @return array{0: Event, 1: EventAttendee, 2: User, 3: Faction}
 */
function teamOfOneSoFar(array $eventAttributes = []): array
{
    $event = Event::factory()->published()->create([
        'attendee_size' => 2,
        ...$eventAttributes,
    ]);
    $captain = User::factory()->create();
    $attendee = EventAttendee::factory()->for($event)->withMember($captain)->create(['name' => 'Sons of Terra']);

    return [$event, $attendee, $captain, Faction::factory()->create(['game_system_id' => $event->game_system_id])];
}

test('a captain adds their partner, who is invited', function () {
    Notification::fake();

    [$event, $attendee, $captain, $faction] = teamOfOneSoFar();

    $this->actingAs($captain)
        ->postJson(route('events.attendees.members.store', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'name' => 'Tarik Torgaddon',
            'email' => 'tarik@example.com',
            'faction_id' => $faction->id,
        ])
        ->assertCreated();

    $partner = User::where('email', 'tarik@example.com')->firstOrFail();

    expect($attendee->fresh()->members)->toHaveCount(2)
        ->and($partner->isClaimed())->toBeFalse();

    Notification::assertSentTo($partner, EventInviteNotification::class);
});

test('a party cannot grow past the size the event competes in', function () {
    Notification::fake();

    [$event, $attendee, $captain, $faction] = teamOfOneSoFar();
    $attendee->members()->attach(User::factory()->create(), ['event_id' => $event->id]);

    $this->actingAs($captain)
        ->postJson(route('events.attendees.members.store', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'email' => 'third@example.com',
            'faction_id' => $faction->id,
        ])
        ->assertUnprocessable();

    expect($attendee->fresh()->members)->toHaveCount(2);
});

test('membership stops changing when registration closes', function () {
    Notification::fake();

    [$event, $attendee, $captain, $faction] = teamOfOneSoFar(['registration_closes_at' => now()->subHour()]);

    $this->actingAs($captain)
        ->postJson(route('events.attendees.members.store', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'email' => 'tarik@example.com',
            'faction_id' => $faction->id,
        ])
        ->assertForbidden();

    expect($attendee->fresh()->members)->toHaveCount(1);
});

test('an organiser amends membership after the deadline', function () {
    Notification::fake();

    [$event, $attendee, , $faction] = teamOfOneSoFar(['registration_closes_at' => now()->subHour()]);
    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    $this->actingAs($organiser)
        ->postJson(route('events.attendees.members.store', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'name' => 'Late Replacement',
            'email' => 'late@example.com',
            'faction_id' => $faction->id,
        ])
        ->assertCreated();

    expect($attendee->fresh()->members)->toHaveCount(2);
});

test('a captain drops a partner before the deadline', function () {
    Notification::fake();

    [$event, $attendee, $captain] = teamOfOneSoFar();
    $partner = User::factory()->create();
    $attendee->members()->attach($partner, ['event_id' => $event->id]);

    $this->actingAs($captain)
        ->deleteJson(route('events.attendees.members.destroy', [
            'event' => $event->slug,
            'attendee' => $attendee->id,
            'member' => $partner->id,
        ]))
        ->assertSuccessful();

    expect($attendee->fresh()->members)->toHaveCount(1);
});

/**
 * A Player is named here even though they have no account of their own yet.
 * That resolves because the parameter is scoped to the Attendee, which binds
 * through the relation rather than through `User::resolveRouteBinding()`.
 */
test('a captain drops a partner who never answered their invitation', function () {
    Notification::fake();

    [$event, $attendee, $captain] = teamOfOneSoFar();
    $partner = User::factory()->unclaimed()->create();
    $attendee->members()->attach($partner, ['event_id' => $event->id]);

    $this->actingAs($captain)
        ->deleteJson(route('events.attendees.members.destroy', [
            'event' => $event->slug,
            'attendee' => $attendee->id,
            'member' => $partner->id,
        ]))
        ->assertSuccessful();

    expect($attendee->fresh()->members)->toHaveCount(1);
});

/**
 * Sends the partner invitation through the API and returns the plain token
 * that only the email ever sees. Requires Notification::fake().
 */
function invitePartnerAndCaptureToken(Event $event, EventAttendee $attendee, User $captain, string $email): string
{
    test()->actingAs($captain)
        ->postJson(route('events.attendees.members.store', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'name' => 'Tarik Torgaddon',
            'email' => $email,
        ])
        ->assertCreated();

    $token = null;

    Notification::assertSentTo(
        User::where('email', $email)->firstOrFail(),
        function (EventInviteNotification $notification) use (&$token): bool {
            $token = $notification->plainToken;

            return true;
        },
    );

    expect($token)->not->toBeNull();

    return $token;
}

test("a removed partner's invitation stops opening the event", function () {
    Notification::fake();

    [$event, $attendee, $captain] = teamOfOneSoFar();
    $token = invitePartnerAndCaptureToken($event, $attendee, $captain, 'tarik@example.com');
    $partner = User::where('email', 'tarik@example.com')->firstOrFail();

    $this->actingAs($captain)
        ->deleteJson(route('events.attendees.members.destroy', [
            'event' => $event->slug,
            'attendee' => $attendee->id,
            'member' => $partner->id,
        ]))
        ->assertSuccessful();

    $this->getJson(route('invites.show', ['token' => $token]))
        ->assertStatus(410)
        ->assertJsonPath('code', 'invite_revoked');
});

test('removing a partner leaves every other invitation working', function () {
    Notification::fake();

    [$event, $attendee, $captain] = teamOfOneSoFar(['attendee_size' => 3]);
    invitePartnerAndCaptureToken($event, $attendee, $captain, 'tarik@example.com');
    $teamMatesToken = invitePartnerAndCaptureToken($event, $attendee, $captain, 'saul@example.com');
    $partner = User::where('email', 'tarik@example.com')->firstOrFail();

    [$otherEvent, $otherAttendee, $otherCaptain] = teamOfOneSoFar();
    $otherEventsToken = invitePartnerAndCaptureToken($otherEvent, $otherAttendee, $otherCaptain, 'tarik@example.com');

    $this->actingAs($captain)
        ->deleteJson(route('events.attendees.members.destroy', [
            'event' => $event->slug,
            'attendee' => $attendee->id,
            'member' => $partner->id,
        ]))
        ->assertSuccessful();

    $this->getJson(route('invites.show', ['token' => $teamMatesToken]))->assertSuccessful();
    $this->getJson(route('invites.show', ['token' => $otherEventsToken]))->assertSuccessful();
});

test('a removed partner keeps an invitation to enter the event on their own', function () {
    Notification::fake();

    [$event, $attendee, $captain] = teamOfOneSoFar();
    invitePartnerAndCaptureToken($event, $attendee, $captain, 'tarik@example.com');
    $partner = User::where('email', 'tarik@example.com')->firstOrFail();

    app(SendEventInvite::class)->handle($event, 'tarik@example.com', EventInviteRole::Captain);
    $captainToken = null;
    Notification::assertSentTo($partner, function (EventInviteNotification $notification) use (&$captainToken): bool {
        if ($notification->role !== EventInviteRole::Captain) {
            return false;
        }

        $captainToken = $notification->plainToken;

        return true;
    });

    $this->actingAs($captain)
        ->deleteJson(route('events.attendees.members.destroy', [
            'event' => $event->slug,
            'attendee' => $attendee->id,
            'member' => $partner->id,
        ]))
        ->assertSuccessful();

    $this->getJson(route('invites.show', ['token' => $captainToken]))
        ->assertSuccessful()
        ->assertJsonPath('data.role', 'captain');
});

test('a captain corrects the name and faction of a partner who has not answered', function () {
    Notification::fake();

    [$event, $attendee, $captain, $faction] = teamOfOneSoFar();
    $partner = User::factory()->unclaimed()->create(['name' => 'Tarik Torgadon', 'email' => 'tarik@example.com']);
    $attendee->members()->attach($partner, ['event_id' => $event->id]);
    $membership = $attendee->fresh()->memberships->firstWhere('user_id', $partner->id);

    $this->actingAs($captain)
        ->patchJson(route('events.attendees.members.update', [
            'event' => $event->slug,
            'attendee' => $attendee->id,
            'membership' => $membership->id,
        ]), ['name' => 'Tarik Torgaddon', 'faction_id' => $faction->id])
        ->assertSuccessful();

    expect($partner->fresh()->name)->toBe('Tarik Torgaddon')
        ->and($membership->fresh()->faction_id)->toBe($faction->id);
});

test('a corrected address moves the seat and kills the credential sent to the old one', function () {
    Notification::fake();

    [$event, $attendee, $captain, $faction] = teamOfOneSoFar();

    $this->actingAs($captain)
        ->postJson(route('events.attendees.members.store', ['event' => $event->slug, 'attendee' => $attendee->id]), [
            'name' => 'Tarik Torgaddon',
            'email' => 'tarik@exmaple.com',
            'faction_id' => $faction->id,
        ])
        ->assertCreated();

    $mistyped = User::where('email', 'tarik@exmaple.com')->firstOrFail();
    $membership = $attendee->fresh()->memberships->firstWhere('user_id', $mistyped->id);

    $this->actingAs($captain)
        ->patchJson(route('events.attendees.members.update', [
            'event' => $event->slug,
            'attendee' => $attendee->id,
            'membership' => $membership->id,
        ]), ['email' => 'tarik@example.com'])
        ->assertSuccessful();

    $corrected = User::where('email', 'tarik@example.com')->firstOrFail();

    // The seat travels rather than being rebuilt, so the Faction entered
    // against it survives the correction.
    expect($membership->fresh()->user_id)->toBe($corrected->id)
        ->and($membership->fresh()->faction_id)->toBe($faction->id)
        ->and($attendee->fresh()->members)->toHaveCount(2)
        ->and(EventInvite::where('user_id', $mistyped->id)->firstOrFail()->isUsable())->toBeFalse();

    Notification::assertSentTo($corrected, EventInviteNotification::class);
});

test('a partner who has claimed their account keeps their own details', function () {
    Notification::fake();

    [$event, $attendee, $captain, $faction] = teamOfOneSoFar();
    $partner = User::factory()->create(['name' => 'Tarik Torgaddon']);
    $attendee->members()->attach($partner, ['event_id' => $event->id]);
    $membership = $attendee->fresh()->memberships->firstWhere('user_id', $partner->id);

    $this->actingAs($captain)
        ->patchJson(route('events.attendees.members.update', [
            'event' => $event->slug,
            'attendee' => $attendee->id,
            'membership' => $membership->id,
        ]), ['name' => 'Somebody Else', 'faction_id' => $faction->id])
        ->assertForbidden();

    expect($partner->fresh()->name)->toBe('Tarik Torgaddon');
});

test('an organiser corrects the faction of a player who has claimed their account', function (string $corrector, bool $listLocked) {
    Notification::fake();

    [$event, $attendee, , $faction] = teamOfOneSoFar();
    $partner = User::factory()->create();
    $attendee->members()->attach($partner, ['event_id' => $event->id]);
    $membership = $attendee->fresh()->memberships->firstWhere('user_id', $partner->id);

    // A locked list does not stop an Organiser: they are the ones who unlock
    // lists, and this is how a wrong Faction is fixed on the day.
    $membership->update(['army_list_submitted_at' => $listLocked ? now() : null]);

    $this->actingAs($corrector === 'admin' ? User::factory()->admin()->create() : coOrganiserOf($event))
        ->patchJson(route('events.attendees.members.update', [
            'event' => $event->slug,
            'attendee' => $attendee->id,
            'membership' => $membership->id,
        ]), ['faction_id' => $faction->id])
        ->assertSuccessful();

    expect($membership->fresh()->faction_id)->toBe($faction->id);

    Notification::assertNothingSent();
})->with(['co-organiser', 'admin'])->with(['open list' => false, 'locked list' => true]);

test('a team mate cannot change the faction of a partner who has claimed their account', function () {
    Notification::fake();

    [$event, $attendee, $captain, $faction] = teamOfOneSoFar();
    $partner = User::factory()->create();
    $attendee->members()->attach($partner, ['event_id' => $event->id]);
    $membership = $attendee->fresh()->memberships->firstWhere('user_id', $partner->id);

    $this->actingAs($captain)
        ->patchJson(route('events.attendees.members.update', [
            'event' => $event->slug,
            'attendee' => $attendee->id,
            'membership' => $membership->id,
        ]), ['faction_id' => $faction->id])
        ->assertForbidden()
        ->assertJsonPath('message', 'That player has claimed their account, so only they can change their details.');

    expect($membership->fresh()->faction_id)->toBeNull();
});

test('an organiser cannot send the name or address of a player who has claimed their account', function (string $field) {
    Notification::fake();

    [$event, $attendee, , $faction] = teamOfOneSoFar();
    $partner = User::factory()->create(['name' => 'Tarik Torgaddon', 'email' => 'tarik@example.com']);
    $attendee->members()->attach($partner, ['event_id' => $event->id]);
    $membership = $attendee->fresh()->memberships->firstWhere('user_id', $partner->id);

    // Refused on presence: even the value they already hold is not the
    // Organiser's to send.
    $this->actingAs(organiserOf($event))
        ->patchJson(route('events.attendees.members.update', [
            'event' => $event->slug,
            'attendee' => $attendee->id,
            'membership' => $membership->id,
        ]), [$field => $partner->{$field}, 'faction_id' => $faction->id])
        ->assertForbidden()
        ->assertJsonPath('message', 'That player has claimed their account, so only they can change their name and email.');

    expect($membership->fresh()->faction_id)->toBeNull();
})->with(['name', 'email']);

test('an address already entered in this event is refused', function () {
    Notification::fake();

    [$event, $attendee, $captain] = teamOfOneSoFar();
    $partner = User::factory()->unclaimed()->create(['email' => 'tarik@example.com']);
    $attendee->members()->attach($partner, ['event_id' => $event->id]);
    $membership = $attendee->fresh()->memberships->firstWhere('user_id', $partner->id);

    $rival = User::factory()->create(['email' => 'loken@example.com']);
    EventAttendee::factory()->for($event)->withMember($rival)->create();

    $this->actingAs($captain)
        ->patchJson(route('events.attendees.members.update', [
            'event' => $event->slug,
            'attendee' => $attendee->id,
            'membership' => $membership->id,
        ]), ['email' => 'loken@example.com'])
        ->assertUnprocessable()
        ->assertJsonValidationErrorFor('email');

    expect($membership->fresh()->user_id)->toBe($partner->id);
});

test('a captain sends a waiting partner their invitation again', function () {
    Notification::fake();

    [$event, $attendee, $captain] = teamOfOneSoFar();
    $partner = User::factory()->unclaimed()->create(['email' => 'tarik@example.com']);
    $attendee->members()->attach($partner, ['event_id' => $event->id]);
    $membership = $attendee->fresh()->memberships->firstWhere('user_id', $partner->id);

    $this->actingAs($captain)
        ->postJson(route('events.attendees.members.invite', [
            'event' => $event->slug,
            'attendee' => $attendee->id,
            'membership' => $membership->id,
        ]))
        ->assertSuccessful();

    Notification::assertSentTo($partner, EventInviteNotification::class);
});

test('the team sees which of its players is still waiting to answer', function () {
    Notification::fake();

    [$event, $attendee, $captain] = teamOfOneSoFar();
    $partner = User::factory()->unclaimed()->create();
    $attendee->members()->attach($partner, ['event_id' => $event->id]);

    $members = $this->actingAs($captain)
        ->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $attendee->id]))
        ->assertSuccessful()
        ->json('data.members');

    expect(collect($members)->firstWhere('id', $captain->id)['invite_outstanding'])->toBeFalse()
        ->and(collect($members)->firstWhere('id', $partner->id)['invite_outstanding'])->toBeTrue()
        ->and(collect($members)->firstWhere('id', $partner->id)['membership_id'])->not->toBeNull();
});

test('the address an unanswered invitation went to comes back, and a claimed one does not', function () {
    Notification::fake();

    [$event, $attendee, $captain] = teamOfOneSoFar();
    $partner = User::factory()->unclaimed()->create(['email' => 'tarik@exmaple.com']);
    $attendee->members()->attach($partner, ['event_id' => $event->id]);

    $members = $this->actingAs($captain)
        ->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $attendee->id]))
        ->assertSuccessful()
        ->json('data.members');

    // The Captain typed it and may have mistyped it, so it is theirs to see
    // and correct until it is answered. Their own claimed address is not sent
    // back to them, and nor is any other claimed Player's.
    expect(collect($members)->firstWhere('id', $partner->id)['email'])->toBe('tarik@exmaple.com')
        ->and(collect($members)->firstWhere('id', $captain->id))->not->toHaveKey('email');
});

test('a stranger is told nothing about who has answered', function () {
    Notification::fake();

    [$event, $attendee] = teamOfOneSoFar();

    $members = $this->actingAs(User::factory()->create())
        ->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $attendee->id]))
        ->assertSuccessful()
        ->json('data.members');

    expect($members[0])->not->toHaveKey('invite_outstanding')
        ->and($members[0])->not->toHaveKey('membership_id');
});
