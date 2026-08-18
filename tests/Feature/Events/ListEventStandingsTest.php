<?php

use App\Models\Club;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventScoreType;
use App\Models\EventStanding;
use App\Models\EventStandingScore;
use App\Models\Faction;
use App\Models\User;

test('it returns standings ordered by position', function () {
    $event = Event::factory()->published()->standingsVisible()->create();

    $attendee1 = EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'First Place']))->create();
    $attendee2 = EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'Second Place']))->create();
    $attendee3 = EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'Third Place']))->create();

    EventStanding::factory()->for($event)->for($attendee3, 'attendee')->create(['position' => 3]);
    EventStanding::factory()->for($event)->for($attendee1, 'attendee')->create(['position' => 1]);
    EventStanding::factory()->for($event)->for($attendee2, 'attendee')->create(['position' => 2]);

    $response = $this->getJson(route('events.standings.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonStructure(['data', 'links', 'meta'])
        ->assertJsonCount(3, 'data');

    expect(collect($response->json('data'))->pluck('position')->all())
        ->toEqual([1, 2, 3]);

    expect($response->json('data.0.attendee.name'))->toBe('First Place');
});

test('it returns 404 when standings_visible is false', function () {
    $event = Event::factory()->published()->create();

    $this->getJson(route('events.standings.index', ['event' => $event->slug]))
        ->assertNotFound();
});

test('it returns 404 for non-publicly-visible events', function (string $state) {
    $event = Event::factory()->{$state}()->standingsVisible()->create();

    $this->getJson(route('events.standings.index', ['event' => $event->slug]))
        ->assertNotFound();
})->with(['draft', 'cancelled']);

test('it includes attendee info and score values with type metadata', function () {
    $event = Event::factory()->published()->standingsVisible()->create();
    $scoreType = EventScoreType::factory()->for($event)->create([
        'name' => 'Battle Points',
        'slug' => 'battle-points',
        'sort_direction' => 'desc',
    ]);

    $user = User::factory()->create(['name' => 'Alice']);
    $attendee = EventAttendee::factory()->for($event)->withMember($user)->create();
    $standing = EventStanding::factory()->for($event)->for($attendee, 'attendee')->create(['position' => 1]);
    EventStandingScore::factory()->for($standing, 'standing')->for($scoreType, 'scoreType')->create(['value' => 75.50]);

    $response = $this->getJson(route('events.standings.index', ['event' => $event->slug]))
        ->assertSuccessful();

    $data = $response->json('data.0');
    expect($data['position'])->toBe(1);
    expect($data['attendee']['name'])->toBe('Alice');
    expect($data['scores'][0]['value'])->toBe('75.50');
    expect($data['scores'][0]['score_type']['name'])->toBe('Battle Points');
    expect($data['scores'][0]['score_type']['slug'])->toBe('battle-points');
    expect($data['scores'][0]['score_type']['sort_direction'])->toBe('desc');
});

test('it sorts by score type value descending when sort_direction is desc', function () {
    $event = Event::factory()->published()->standingsVisible()->create();
    $scoreType = EventScoreType::factory()->for($event)->create([
        'slug' => 'battle-points',
        'sort_direction' => 'desc',
    ]);

    $lowUser = User::factory()->create(['name' => 'Low Scorer']);
    $highUser = User::factory()->create(['name' => 'High Scorer']);

    $lowAttendee = EventAttendee::factory()->for($event)->withMember($lowUser)->create();
    $highAttendee = EventAttendee::factory()->for($event)->withMember($highUser)->create();

    $lowStanding = EventStanding::factory()->for($event)->for($lowAttendee, 'attendee')->create(['position' => 1]);
    $highStanding = EventStanding::factory()->for($event)->for($highAttendee, 'attendee')->create(['position' => 2]);

    EventStandingScore::factory()->for($lowStanding, 'standing')->for($scoreType, 'scoreType')->create(['value' => 10]);
    EventStandingScore::factory()->for($highStanding, 'standing')->for($scoreType, 'scoreType')->create(['value' => 90]);

    $response = $this->getJson(route('events.standings.index', ['event' => $event->slug, 'sort_by' => 'battle-points']))
        ->assertSuccessful();

    // sort_by overrides position order — High Scorer (90) comes first in desc
    $names = collect($response->json('data'))->pluck('attendee.name')->all();
    expect($names)->toEqual(['High Scorer', 'Low Scorer']);
});

test('it sorts by score type value ascending when sort_direction is asc', function () {
    $event = Event::factory()->published()->standingsVisible()->create();
    $scoreType = EventScoreType::factory()->for($event)->create([
        'slug' => 'penalties',
        'sort_direction' => 'asc',
    ]);

    $lowUser = User::factory()->create(['name' => 'Few Penalties']);
    $highUser = User::factory()->create(['name' => 'Many Penalties']);

    $lowAttendee = EventAttendee::factory()->for($event)->withMember($lowUser)->create();
    $highAttendee = EventAttendee::factory()->for($event)->withMember($highUser)->create();

    $lowStanding = EventStanding::factory()->for($event)->for($lowAttendee, 'attendee')->create(['position' => 2]);
    $highStanding = EventStanding::factory()->for($event)->for($highAttendee, 'attendee')->create(['position' => 1]);

    EventStandingScore::factory()->for($lowStanding, 'standing')->for($scoreType, 'scoreType')->create(['value' => 5]);
    EventStandingScore::factory()->for($highStanding, 'standing')->for($scoreType, 'scoreType')->create(['value' => 50]);

    $response = $this->getJson(route('events.standings.index', ['event' => $event->slug, 'sort_by' => 'penalties']))
        ->assertSuccessful();

    // sort_by overrides position order — Few Penalties (5) comes first in asc
    $names = collect($response->json('data'))->pluck('attendee.name')->all();
    expect($names)->toEqual(['Few Penalties', 'Many Penalties']);
});

test('it returns 422 for invalid sort_by value', function () {
    $event = Event::factory()->published()->standingsVisible()->create();
    EventScoreType::factory()->for($event)->create(['slug' => 'battle-points']);

    $this->getJson(route('events.standings.index', ['event' => $event->slug, 'sort_by' => 'nonexistent-type']))
        ->assertStatus(422);
});

test('it searches by attendee user name', function () {
    $event = Event::factory()->published()->standingsVisible()->create();

    $match = EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'Alice Anderson']))->create();
    $miss = EventAttendee::factory()->for($event)->withMember(User::factory()->create(['name' => 'Bob Brown']))->create();

    EventStanding::factory()->for($event)->for($match, 'attendee')->create(['position' => 1]);
    EventStanding::factory()->for($event)->for($miss, 'attendee')->create(['position' => 2]);

    $this->getJson(route('events.standings.index', ['event' => $event->slug, 'search' => 'alice']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.attendee.name', 'Alice Anderson');
});

test('it searches by faction name', function () {
    $event = Event::factory()->published()->standingsVisible()->create();

    $matchFaction = Faction::factory()->create(['name' => 'Space Marines']);
    $missFaction = Faction::factory()->create(['name' => 'Tyranids']);

    $match = EventAttendee::factory()->for($event)->withMember(null, ['faction_id' => $matchFaction->id])->create();
    $miss = EventAttendee::factory()->for($event)->withMember(null, ['faction_id' => $missFaction->id])->create();

    EventStanding::factory()->for($event)->for($match, 'attendee')->create(['position' => 1]);
    EventStanding::factory()->for($event)->for($miss, 'attendee')->create(['position' => 2]);

    $this->getJson(route('events.standings.index', ['event' => $event->slug, 'search' => 'marines']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('it searches by club name', function () {
    $event = Event::factory()->published()->standingsVisible()->create();

    $club = Club::factory()->create(['name' => 'London Warlords']);
    $user = User::factory()->create();
    $user->clubs()->attach($club);

    $match = EventAttendee::factory()->for($event)->withMember($user)->create();
    $miss = EventAttendee::factory()->for($event)->withMember()->create();

    EventStanding::factory()->for($event)->for($match, 'attendee')->create(['position' => 1]);
    EventStanding::factory()->for($event)->for($miss, 'attendee')->create(['position' => 2]);

    $this->getJson(route('events.standings.index', ['event' => $event->slug, 'search' => 'warlords']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});
