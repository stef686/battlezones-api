<?php

use App\Enums\EventOrganiserRole;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\User;

test('a player submits their own list, which locks on submission', function () {
    $event = Event::factory()->active()->create(['attendee_size' => 2]);
    $player = User::factory()->create();
    EventAttendee::factory()->for($event)->withMember($player)->create();

    $this->actingAs($player)
        ->putJson(route('events.army-list.update', ['event' => $event->slug]), [
            'army_list' => '2000pts Ultramarines',
        ])
        ->assertSuccessful();

    $this->actingAs($player)
        ->putJson(route('events.army-list.update', ['event' => $event->slug]), [
            'army_list' => 'Second thoughts',
        ])
        ->assertForbidden();

    expect($event->attendees()->firstOrFail()->memberships()->firstOrFail()->army_list)
        ->toBe('2000pts Ultramarines');
});

/**
 * A doubles team plus a rival Player who also attends the Event.
 *
 * @return array{0: Event, 1: EventAttendee, 2: User, 3: User, 4: User}
 */
function fieldOfTwoTeams(): array
{
    $event = Event::factory()->active()->create(['attendee_size' => 2]);

    $captain = User::factory()->create();
    $partner = User::factory()->create();
    $team = EventAttendee::factory()->for($event)->withMember($captain)->withMember($partner)->create();

    $rival = User::factory()->create();
    EventAttendee::factory()->for($event)->withMember($rival)->create();

    return [$event, $team, $captain, $partner, $rival];
}

function submitArmyList(User $player, Event $event, string $list): void
{
    test()->actingAs($player)
        ->putJson(route('events.army-list.update', ['event' => $event->slug]), ['army_list' => $list])
        ->assertSuccessful();
}

test('a team stays hidden until every one of its players has submitted', function () {
    [$event, $team, $captain, $partner, $rival] = fieldOfTwoTeams();

    submitArmyList($captain, $event, '2000pts Ultramarines');

    $this->actingAs($rival)
        ->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $team->id]))
        ->assertSuccessful()
        ->assertJsonMissingPath('data.members.0.army_list');

    submitArmyList($partner, $event, '2000pts Sons of Horus');

    $this->actingAs($rival)
        ->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $team->id]))
        ->assertSuccessful()
        ->assertJsonPath('data.members.0.army_list', '2000pts Ultramarines');
});

test('army lists need authentication and attendance to read', function () {
    [$event, $team] = fieldOfTwoTeams();

    // Submitted directly so that nobody is left authenticated: the point of
    // this test is what a stranger and a passer-by can see.
    $team->memberships()->update(['army_list_submitted_at' => now()]);

    $this->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $team->id]))
        ->assertSuccessful()
        ->assertJsonPath('data.name', $team->displayName())
        ->assertJsonMissingPath('data.members.0.army_list');

    $this->actingAs(User::factory()->create())
        ->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $team->id]))
        ->assertSuccessful()
        ->assertJsonMissingPath('data.members.0.army_list');
});

test('an organiser unlocks one list so it can be corrected', function () {
    [$event, $team, $captain] = fieldOfTwoTeams();
    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    submitArmyList($captain, $event, 'Wrong list');

    $this->actingAs($organiser)
        ->postJson(route('events.army-list.unlock', [
            'event' => $event->slug,
            'attendee' => $team->id,
            'member' => $captain->id,
        ]))
        ->assertSuccessful();

    submitArmyList($captain, $event, '2000pts Ultramarines');

    expect($team->memberships()->where('user_id', $captain->id)->firstOrFail()->army_list)
        ->toBe('2000pts Ultramarines');
});

test('an organiser reveals a team whose partner never submitted', function () {
    [$event, $team, $captain, , $rival] = fieldOfTwoTeams();
    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    submitArmyList($captain, $event, '2000pts Ultramarines');

    $this->actingAs($rival)
        ->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $team->id]))
        ->assertJsonMissingPath('data.members.0.army_list');

    $this->actingAs($organiser)
        ->postJson(route('events.army-lists.reveal', ['event' => $event->slug, 'attendee' => $team->id]))
        ->assertSuccessful();

    $this->actingAs($rival)
        ->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $team->id]))
        ->assertJsonPath('data.members.0.army_list', '2000pts Ultramarines');
});

test('only an organiser may unlock or reveal', function () {
    [$event, $team, $captain] = fieldOfTwoTeams();

    submitArmyList($captain, $event, '2000pts Ultramarines');

    $this->actingAs($captain)
        ->postJson(route('events.army-list.unlock', [
            'event' => $event->slug,
            'attendee' => $team->id,
            'member' => $captain->id,
        ]))
        ->assertForbidden();

    $this->actingAs($captain)
        ->postJson(route('events.army-lists.reveal', ['event' => $event->slug, 'attendee' => $team->id]))
        ->assertForbidden();

    expect($team->fresh()->armyListsAreVisible())->toBeFalse();
});

test('a team says which of its players have locked their list in', function () {
    [$event, $team, $captain, $partner] = fieldOfTwoTeams();

    $this->actingAs($captain)
        ->putJson(route('events.army-list.update', ['event' => $event->slug]), ['army_list' => 'Locked in'])
        ->assertSuccessful();

    $members = collect($this->actingAs($captain)
        ->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $team->id]))
        ->assertSuccessful()
        ->json('data.members'))
        ->keyBy('id');

    // A Player has to be able to tell, without guessing, whether what they
    // typed counts as submitted — and an Organiser which list to reopen.
    expect($members[$captain->id]['army_list_locked'])->toBeTrue()
        ->and($members[$partner->id]['army_list_locked'])->toBeFalse();
});

test('a bearer token is enough to read revealed lists on the public attendee endpoint', function () {
    [$event, $team, $captain, $partner, $rival] = fieldOfTwoTeams();

    // Locked without signing anybody in, so nothing about this request's
    // authentication is left over from setting the fixture up.
    $team->memberships()->update(['army_list' => 'Locked in', 'army_list_submitted_at' => now()]);

    // Signed in the way the SPA is: a token on a route that is otherwise
    // public, where the session guard has nobody in it.
    $token = $rival->createToken('their-phone')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson(route('events.attendees.show', ['event' => $event->slug, 'attendee' => $team->id]))
        ->assertSuccessful();

    expect($response->json('data.members.0.army_list'))->toBe('Locked in');
});
