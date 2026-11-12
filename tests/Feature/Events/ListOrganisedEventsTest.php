<?php

use App\Enums\EventOrganiserRole;
use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('an organiser lists the events they run, and none they do not', function () {
    $mine = Event::factory()->published()->create();
    $organiser = organiserOf($mine);
    Event::factory()->published()->create();

    $this->actingAs($organiser)
        ->getJson(route('organised-events.index'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', $mine->slug);
});

test('the list holds every status, drafts and cancelled events included', function () {
    $organiser = User::factory()->create();

    foreach (EventStatus::cases() as $status) {
        $event = Event::factory()->create(['status' => $status]);
        $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);
    }

    $statuses = $this->actingAs($organiser)
        ->getJson(route('organised-events.index'))
        ->assertOk()
        ->json('data.*.status');

    expect($statuses)->toEqualCanonicalizing(array_map(fn (EventStatus $status): string => $status->value, EventStatus::cases()));
});

test('a co-organiser finds the events they help run', function () {
    $event = Event::factory()->draft()->create();

    $this->actingAs(coOrganiserOf($event))
        ->getJson(route('organised-events.index'))
        ->assertOk()
        ->assertJsonPath('data.0.slug', $event->slug);
});

test('the events are in the order they start, not the order they were made', function () {
    $organiser = User::factory()->create();

    foreach (['2026-09-12', '2026-03-07', '2026-06-20'] as $day) {
        Event::factory()->create(['starts_at' => "{$day} 09:00:00", 'ends_at' => "{$day} 18:00:00"])
            ->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);
    }

    $starts = $this->actingAs($organiser)
        ->getJson(route('organised-events.index'))
        ->json('data.*.starts_at');

    expect($starts)->toBe(['2026-03-07T09:00:00Z', '2026-06-20T09:00:00Z', '2026-09-12T09:00:00Z']);
});

test('a guest is asked to sign in', function () {
    $this->getJson(route('organised-events.index'))->assertUnauthorized();
});

test('an unclaimed account, which only plays, organises nothing', function () {
    $event = Event::factory()->published()->create();
    $invited = User::factory()->unclaimed()->create();
    EventAttendee::factory()->for($event)->withMember($invited)->create();

    $this->actingAs($invited)
        ->getJson(route('organised-events.index'))
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('a long list costs the same queries as a short one, game system included', function () {
    $organiser = User::factory()->create();
    $organise = fn (int $count) => Event::factory()->count($count)->create(['max_attendees' => 32])
        ->each(fn (Event $event) => $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]));

    $organise(1);
    $this->actingAs($organiser);

    DB::enableQueryLog();
    $this->getJson(route('organised-events.index'))->assertOk();
    $few = count(DB::getQueryLog());

    $organise(5);

    DB::flushQueryLog();
    $this->getJson(route('organised-events.index'))
        ->assertOk()
        ->assertJsonCount(6, 'data')
        ->assertJsonStructure(['data' => [['game_system' => ['id', 'name']]]]);

    expect(count(DB::getQueryLog()))->toBe($few);
});

test('events starting together keep one order, the order they were made in', function () {
    $organiser = User::factory()->create();

    $made = collect(range(1, 4))->map(function () use ($organiser): Event {
        $event = Event::factory()->create(['starts_at' => '2026-09-12 09:00:00', 'ends_at' => '2026-09-12 18:00:00']);
        $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

        return $event;
    });

    $this->actingAs($organiser)
        ->getJson(route('organised-events.index'))
        ->assertOk()
        ->assertJsonPath('data.*.id', $made->pluck('id')->all());
});
