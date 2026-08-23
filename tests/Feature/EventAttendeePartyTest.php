<?php

use App\Actions\Events\AddAttendeeMember;
use App\Exceptions\AttendeeMemberAlreadyEntered;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\Faction;
use App\Models\User;
use Illuminate\Database\QueryException;

test('an attendee carries a faction and army list for each of its members', function () {
    $event = Event::factory()->create(['attendee_size' => 2]);
    $attendee = EventAttendee::factory()->for($event)->create(['name' => 'Sons of Terra']);

    $loyalist = User::factory()->create();
    $traitor = User::factory()->create();
    $ultramarines = Faction::factory()->create(['name' => 'Ultramarines']);
    $sonsOfHorus = Faction::factory()->create(['name' => 'Sons of Horus']);

    $attendee->members()->attach($loyalist, [
        'event_id' => $event->id,
        'faction_id' => $ultramarines->id,
        'army_list' => '2000pts Ultramarines',
    ]);
    $attendee->members()->attach($traitor, [
        'event_id' => $event->id,
        'faction_id' => $sonsOfHorus->id,
        'army_list' => '2000pts Sons of Horus',
    ]);

    $members = $attendee->fresh()->members;

    expect($members)->toHaveCount(2)
        ->and($members->firstWhere('id', $loyalist->id)->membership->faction_id)->toBe($ultramarines->id)
        ->and($members->firstWhere('id', $traitor->id)->membership->army_list)->toBe('2000pts Sons of Horus');
});

test('a party competes under its own name', function () {
    $attendee = EventAttendee::factory()->withMember()->create(['name' => 'Sons of Terra']);

    expect($attendee->displayName())->toBe('Sons of Terra');
});

test('a lone player competes under their own name', function () {
    $player = User::factory()->create(['name' => 'Loken']);
    $attendee = EventAttendee::factory()->withMember($player)->create(['name' => null]);

    expect($attendee->displayName())->toBe('Loken');
});

test('a player cannot enter the same event twice', function () {
    $event = Event::factory()->create();
    $player = User::factory()->create();

    EventAttendee::factory()->for($event)->withMember($player)->create();
    $second = EventAttendee::factory()->for($event)->create();

    expect(fn () => $second->members()->attach($player, ['event_id' => $event->id]))
        ->toThrow(QueryException::class);
});

test('a player may enter a different event', function () {
    $player = User::factory()->create();

    EventAttendee::factory()->withMember($player)->create();
    $other = EventAttendee::factory()->create();
    $other->members()->attach($player, ['event_id' => $other->event_id]);

    expect($other->fresh()->members)->toHaveCount(1);
});

test('adding a member derives the event from the attendee', function () {
    $event = Event::factory()->create();
    $attendee = EventAttendee::factory()->for($event)->create();
    $player = User::factory()->create();
    $faction = Faction::factory()->create();

    $membership = app(AddAttendeeMember::class)->handle($attendee, $player, $faction);

    expect($membership->event_id)->toBe($event->id)
        ->and($membership->faction_id)->toBe($faction->id)
        ->and($membership->army_list)->toBeNull();
});

test('adding a member already in the event is rejected', function () {
    $event = Event::factory()->create();
    $player = User::factory()->create();
    EventAttendee::factory()->for($event)->withMember($player)->create();

    $attendee = EventAttendee::factory()->for($event)->create();

    expect(fn () => app(AddAttendeeMember::class)->handle($attendee, $player))
        ->toThrow(AttendeeMemberAlreadyEntered::class);
});
