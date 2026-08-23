<?php

use App\Models\Event;
use App\Models\EventScheduleBlock;
use App\Models\Round;
use App\Models\User;

test('an organiser adds a block and players read it grouped by day in event-local time', function () {
    $event = Event::factory()->published()->create(['timezone' => 'Europe/London']);
    $organiser = organiserOf($event);

    $this->actingAs($organiser)
        ->postJson(route('events.schedule.store', ['event' => $event->slug]), [
            'label' => 'Registration',
            'type' => 'info',
            'starts_at' => '2026-07-11T08:00:00+01:00',
            'ends_at' => '2026-07-11T09:00:00+01:00',
        ])
        ->assertSuccessful();

    $this->actingAs($organiser)
        ->postJson(route('events.schedule.store', ['event' => $event->slug]), [
            'label' => 'Evening Social',
            'type' => 'info',
            'starts_at' => '2026-07-11T22:30:00+01:00',
            'ends_at' => '2026-07-11T23:30:00+01:00',
        ])
        ->assertSuccessful();

    $response = $this->getJson(route('events.schedule.index', ['event' => $event->slug]))
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.date'))->toBe('2026-07-11')
        ->and(collect($response->json('data.0.blocks'))->pluck('label')->all())->toEqual(['Registration', 'Evening Social'])
        ->and(EventScheduleBlock::query()->count())->toBe(2);
});

test('editing a block time moves it between days', function () {
    $event = Event::factory()->published()->create(['timezone' => 'Europe/London']);
    $organiser = organiserOf($event);

    $block = EventScheduleBlock::factory()->for($event)->create([
        'label' => 'Evening Social',
        'starts_at' => '2026-07-11T21:00:00+01:00',
        'ends_at' => '2026-07-11T23:00:00+01:00',
    ]);

    expect($block->day())->toBe('2026-07-11');

    $this->actingAs($organiser)
        ->patchJson(route('events.schedule.update', ['event' => $event->slug, 'block' => $block->id]), [
            'starts_at' => '2026-07-12T00:30:00+01:00',
            'ends_at' => '2026-07-12T01:30:00+01:00',
        ])
        ->assertSuccessful();

    $response = $this->getJson(route('events.schedule.index', ['event' => $event->slug]))
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.date'))->toBe('2026-07-12');
});

test('a round block links to its round and an info block carries no target', function () {
    $event = Event::factory()->published()->create(['timezone' => 'Europe/London']);
    $organiser = organiserOf($event);
    $round = Round::factory()->for($event)->create(['number' => 1, 'name' => 'Round 1']);

    $this->actingAs($organiser)
        ->postJson(route('events.schedule.store', ['event' => $event->slug]), [
            'label' => 'Round 1',
            'type' => 'round',
            'round_id' => $round->id,
            'starts_at' => '2026-07-11T09:00:00+01:00',
            'ends_at' => '2026-07-11T11:30:00+01:00',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.round.number', 1);

    $this->actingAs($organiser)
        ->postJson(route('events.schedule.store', ['event' => $event->slug]), [
            'label' => 'Lunch',
            'type' => 'info',
            'round_id' => $round->id,
            'starts_at' => '2026-07-11T12:00:00+01:00',
            'ends_at' => '2026-07-11T13:00:00+01:00',
        ])
        ->assertJsonValidationErrors('round_id');

    $this->actingAs($organiser)
        ->postJson(route('events.schedule.store', ['event' => $event->slug]), [
            'label' => 'Round 2',
            'type' => 'round',
            'starts_at' => '2026-07-11T14:00:00+01:00',
            'ends_at' => '2026-07-11T16:30:00+01:00',
        ])
        ->assertJsonValidationErrors('round_id');
});

test('a round block cannot point at another event round', function () {
    $event = Event::factory()->published()->create();
    $organiser = organiserOf($event);
    $elsewhere = Round::factory()->create();

    $this->actingAs($organiser)
        ->postJson(route('events.schedule.store', ['event' => $event->slug]), [
            'label' => 'Round 1',
            'type' => 'round',
            'round_id' => $elsewhere->id,
            'starts_at' => '2026-07-11T09:00:00+01:00',
            'ends_at' => '2026-07-11T11:30:00+01:00',
        ])
        ->assertJsonValidationErrors('round_id');
});

test('players cannot write the schedule', function () {
    $event = Event::factory()->published()->create();
    $player = User::factory()->create();
    $block = EventScheduleBlock::factory()->for($event)->create();

    $this->actingAs($player)
        ->postJson(route('events.schedule.store', ['event' => $event->slug]), [
            'label' => 'Nonsense',
            'type' => 'info',
            'starts_at' => '2026-07-11T09:00:00+01:00',
            'ends_at' => '2026-07-11T10:00:00+01:00',
        ])
        ->assertForbidden();

    $this->actingAs($player)
        ->patchJson(route('events.schedule.update', ['event' => $event->slug, 'block' => $block->id]), ['label' => 'Nonsense'])
        ->assertForbidden();

    $this->actingAs($player)
        ->deleteJson(route('events.schedule.destroy', ['event' => $event->slug, 'block' => $block->id]))
        ->assertForbidden();
});

test('an organiser deletes a block', function () {
    $event = Event::factory()->published()->create();
    $organiser = organiserOf($event);
    $block = EventScheduleBlock::factory()->for($event)->create();

    $this->actingAs($organiser)
        ->deleteJson(route('events.schedule.destroy', ['event' => $event->slug, 'block' => $block->id]))
        ->assertNoContent();

    expect(EventScheduleBlock::query()->find($block->id))->toBeNull();
});

test('an organiser reorders blocks that start at the same time', function () {
    $event = Event::factory()->published()->create(['timezone' => 'Europe/London']);
    $organiser = organiserOf($event);

    $awards = EventScheduleBlock::factory()->for($event)->create([
        'label' => 'Awards',
        'starts_at' => '2026-07-12T16:00:00+01:00',
        'ends_at' => '2026-07-12T17:00:00+01:00',
    ]);
    $raffle = EventScheduleBlock::factory()->for($event)->create([
        'label' => 'Raffle',
        'starts_at' => '2026-07-12T16:00:00+01:00',
        'ends_at' => '2026-07-12T17:00:00+01:00',
    ]);

    $this->actingAs($organiser)
        ->postJson(route('events.schedule.reorder', ['event' => $event->slug]), [
            'block_ids' => [$raffle->id, $awards->id],
        ])
        ->assertSuccessful();

    $response = $this->getJson(route('events.schedule.index', ['event' => $event->slug]))
        ->assertSuccessful();

    expect(collect($response->json('data.0.blocks'))->pluck('label')->all())->toEqual(['Raffle', 'Awards']);
});

test('a full weekend schedule reads back grouped by day in order', function () {
    $event = Event::factory()->published()->create(['timezone' => 'Europe/London']);
    $organiser = organiserOf($event);

    $rounds = collect(range(1, 5))->map(fn (int $number) => Round::factory()->for($event)->create([
        'number' => $number,
        'name' => "Round {$number}",
    ]));

    $saturday = [
        ['Registration', 'info', null, '08:30', '09:30'],
        ['Round 1', 'round', $rounds[0]->id, '09:30', '12:00'],
        ['Lunch', 'info', null, '12:00', '13:00'],
        ['Round 2', 'round', $rounds[1]->id, '13:00', '15:30'],
        ['Round 3', 'round', $rounds[2]->id, '16:00', '18:30'],
        ['Painting Voting', 'painting_voting', null, '18:30', '20:00'],
    ];

    $sunday = [
        ['Round 4', 'round', $rounds[3]->id, '09:00', '11:30'],
        ['Lunch', 'info', null, '11:30', '12:30'],
        ['Round 5', 'round', $rounds[4]->id, '12:30', '15:00'],
        ['Awards', 'info', null, '15:30', '16:30'],
    ];

    foreach ([['2026-07-11', $saturday], ['2026-07-12', $sunday]] as [$date, $blocks]) {
        foreach ($blocks as [$label, $type, $roundId, $from, $to]) {
            $payload = [
                'label' => $label,
                'type' => $type,
                'starts_at' => "{$date}T{$from}:00+01:00",
                'ends_at' => "{$date}T{$to}:00+01:00",
            ];

            if ($roundId !== null) {
                $payload['round_id'] = $roundId;
            }

            $this->actingAs($organiser)
                ->postJson(route('events.schedule.store', ['event' => $event->slug]), $payload)
                ->assertSuccessful();
        }
    }

    $response = $this->getJson(route('events.schedule.index', ['event' => $event->slug]))
        ->assertSuccessful();

    expect(collect($response->json('data'))->pluck('date')->all())->toEqual(['2026-07-11', '2026-07-12'])
        ->and(collect($response->json('data.0.blocks'))->pluck('label')->all())->toEqual(array_column($saturday, 0))
        ->and(collect($response->json('data.1.blocks'))->pluck('label')->all())->toEqual(array_column($sunday, 0))
        ->and($response->json('data.0.blocks.5.type'))->toBe('painting_voting')
        ->and($response->json('data.0.blocks.5.round'))->toBeNull();
});

test('the schedule is not readable for a draft event', function () {
    $event = Event::factory()->draft()->create();
    EventScheduleBlock::factory()->for($event)->create();

    $this->getJson(route('events.schedule.index', ['event' => $event->slug]))
        ->assertNotFound();
});
