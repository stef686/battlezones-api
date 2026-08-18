<?php

use App\Enums\RegistrationMode;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

test('settings fall back to declared defaults when none are stored', function () {
    $event = Event::factory()->create();

    expect($event->settings->requiresOpposedAllegiance)->toBeFalse()
        ->and($event->settings->roundCount)->toBeNull()
        ->and($event->settings->standingsVisible)->toBeFalse();
});

test('an event runs singles and open registration unless configured otherwise', function () {
    $event = Event::factory()->create();

    expect($event->attendee_size)->toBe(1)
        ->and($event->registration_mode)->toBe(RegistrationMode::Open)
        ->and($event->timezone)->toBe('UTC');
});

test('registration stays open while no deadline is set', function () {
    $event = Event::factory()->create(['registration_closes_at' => null]);

    expect($event->registrationHasClosed())->toBeFalse();
});

test('registration closes once its deadline has passed', function () {
    $open = Event::factory()->create(['registration_closes_at' => now()->addDay()]);
    $closed = Event::factory()->create(['registration_closes_at' => now()->subMinute()]);

    expect($open->registrationHasClosed())->toBeFalse()
        ->and($closed->registrationHasClosed())->toBeTrue();
});

test('an unrecognised stored setting fails loudly rather than being ignored', function () {
    $event = Event::factory()->create();

    DB::table('events')->where('id', $event->id)->update([
        'settings' => json_encode(['requires_opposed_alegiance' => true]),
    ]);

    expect(fn () => $event->fresh()->settings)
        ->toThrow(InvalidArgumentException::class, 'requires_opposed_alegiance');
});

test('settings survive a save and reload as typed values', function () {
    $event = Event::factory()->create();

    $event->settings = $event->settings->with([
        'requires_opposed_allegiance' => true,
        'round_count' => 4,
    ]);
    $event->save();

    $reloaded = $event->fresh();

    expect($reloaded->settings->requiresOpposedAllegiance)->toBeTrue()
        ->and($reloaded->settings->roundCount)->toBe(4)
        ->and($reloaded->settings->standingsVisible)->toBeFalse();
});
