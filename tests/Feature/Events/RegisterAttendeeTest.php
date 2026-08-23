<?php

use App\Actions\Events\SendEventInvite;
use App\Enums\Allegiance;
use App\Enums\EventInviteRole;
use App\Enums\EventOrganiserRole;
use App\Enums\RegistrationMode;
use App\Models\Event;
use App\Models\Faction;
use App\Models\User;
use App\Notifications\Events\EventInviteNotification;
use Illuminate\Support\Facades\Notification;

/**
 * A doubles Event with two Factions from its own Game System.
 *
 * @return array{0: Event, 1: Faction, 2: Faction}
 */
function doublesEvent(array $attributes = []): array
{
    $event = Event::factory()->published()->create([
        'attendee_size' => 2,
        'registration_mode' => RegistrationMode::Open,
        ...$attributes,
    ]);

    return [
        $event,
        Faction::factory()->create(['game_system_id' => $event->game_system_id]),
        Faction::factory()->create(['game_system_id' => $event->game_system_id]),
    ];
}

test('a captain registers a team and their partner is invited', function () {
    Notification::fake();

    [$event, $ultramarines, $sonsOfHorus] = doublesEvent();
    $captain = User::factory()->create();

    $this->actingAs($captain)
        ->postJson(route('events.attendees.store', ['event' => $event->slug]), [
            'name' => 'Sons of Terra',
            'allegiance' => 'loyalist',
            'players' => [
                [
                    'name' => $captain->name,
                    'email' => $captain->email,
                    'faction_id' => $ultramarines->id,
                    'army_list' => '2000pts Ultramarines',
                ],
                [
                    'name' => 'Tarik Torgaddon',
                    'email' => 'tarik@example.com',
                    'faction_id' => $sonsOfHorus->id,
                ],
            ],
        ])
        ->assertCreated();

    $attendee = $event->attendees()->firstOrFail();
    $partner = User::where('email', 'tarik@example.com')->firstOrFail();

    expect($attendee->name)->toBe('Sons of Terra')
        ->and($attendee->allegiance)->toBe(Allegiance::Loyalist)
        ->and($attendee->members)->toHaveCount(2)
        ->and($partner->isClaimed())->toBeFalse();

    Notification::assertSentTo($partner, EventInviteNotification::class);
});

test('a party must be the size the event competes in', function () {
    Notification::fake();

    [$event, $faction] = doublesEvent();
    $captain = User::factory()->create();

    $this->actingAs($captain)
        ->postJson(route('events.attendees.store', ['event' => $event->slug]), [
            'players' => [
                ['name' => $captain->name, 'email' => $captain->email, 'faction_id' => $faction->id],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('players');

    expect($event->attendees()->count())->toBe(0);
});

test('a player cannot be entered into the same event twice', function () {
    Notification::fake();

    [$event, $ultramarines, $sonsOfHorus] = doublesEvent();
    $captain = User::factory()->create();
    $partner = User::factory()->create();

    $register = fn () => $this->actingAs($captain)
        ->postJson(route('events.attendees.store', ['event' => $event->slug]), [
            'players' => [
                ['email' => $captain->email, 'faction_id' => $ultramarines->id],
                ['email' => $partner->email, 'faction_id' => $sonsOfHorus->id],
            ],
        ]);

    $register()->assertCreated();
    $register()->assertUnprocessable();

    expect($event->attendees()->count())->toBe(1);
});

test('registration is rejected once the deadline has passed', function () {
    Notification::fake();

    [$event, $ultramarines, $sonsOfHorus] = doublesEvent([
        'registration_closes_at' => now()->subHour(),
    ]);
    $captain = User::factory()->create();

    $this->actingAs($captain)
        ->postJson(route('events.attendees.store', ['event' => $event->slug]), [
            'players' => [
                ['email' => $captain->email, 'faction_id' => $ultramarines->id],
                ['email' => 'tarik@example.com', 'faction_id' => $sonsOfHorus->id],
            ],
        ])
        ->assertForbidden();

    expect($event->attendees()->count())->toBe(0);
});

test('an organiser registers a team after the deadline', function () {
    Notification::fake();

    [$event, $ultramarines, $sonsOfHorus] = doublesEvent([
        'registration_closes_at' => now()->subHour(),
    ]);
    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    $this->actingAs($organiser)
        ->postJson(route('events.attendees.store', ['event' => $event->slug]), [
            'name' => 'Latecomers',
            'players' => [
                ['name' => 'Loken', 'email' => 'loken@example.com', 'faction_id' => $ultramarines->id],
                ['name' => 'Tarik', 'email' => 'tarik@example.com', 'faction_id' => $sonsOfHorus->id],
            ],
        ])
        ->assertCreated();

    expect($event->attendees()->count())->toBe(1);
});

test('an invite-only event turns away anyone who was not invited', function () {
    Notification::fake();

    [$event, $ultramarines, $sonsOfHorus] = doublesEvent([
        'registration_mode' => RegistrationMode::InviteOnly,
    ]);
    $stranger = User::factory()->create();

    $body = fn (User $captain): array => [
        'players' => [
            ['email' => $captain->email, 'faction_id' => $ultramarines->id],
            ['email' => 'tarik@example.com', 'faction_id' => $sonsOfHorus->id],
        ],
    ];

    $this->actingAs($stranger)
        ->postJson(route('events.attendees.store', ['event' => $event->slug]), $body($stranger))
        ->assertForbidden();

    $invited = User::factory()->create();
    app(SendEventInvite::class)->handle($event, $invited->email, EventInviteRole::Captain);

    $this->actingAs($invited)
        ->postJson(route('events.attendees.store', ['event' => $event->slug]), $body($invited))
        ->assertCreated();
});

test('a partner accepts their invitation after registration has closed', function () {
    Notification::fake();

    [$event, $ultramarines, $sonsOfHorus] = doublesEvent();
    $captain = User::factory()->create();

    $this->actingAs($captain)
        ->postJson(route('events.attendees.store', ['event' => $event->slug]), [
            'players' => [
                ['email' => $captain->email, 'faction_id' => $ultramarines->id],
                ['name' => 'Tarik', 'email' => 'tarik@example.com', 'faction_id' => $sonsOfHorus->id],
            ],
        ])
        ->assertCreated();

    $partner = User::where('email', 'tarik@example.com')->firstOrFail();

    $token = null;
    Notification::assertSentTo($partner, function (EventInviteNotification $notification) use (&$token): bool {
        $token = $notification->plainToken;

        return true;
    });

    // Closing entry must not orphan a team that already exists.
    $event->forceFill(['registration_closes_at' => now()->subMinute()])->save();

    $this->postJson(route('invites.claim', ['token' => $token]), [
        'password' => 'a-real-password',
        'password_confirmation' => 'a-real-password',
        'device_name' => 'iPhone',
    ])->assertSuccessful();

    expect($partner->fresh()->isClaimed())->toBeTrue();
});

test('an event that opposes allegiances demands one at registration', function () {
    Notification::fake();

    [$event, $ultramarines, $sonsOfHorus] = doublesEvent();
    $event->forceFill(['settings' => $event->settings->with(['requires_opposed_allegiance' => true])])->save();

    $captain = User::factory()->create();

    $this->actingAs($captain)
        ->postJson(route('events.attendees.store', ['event' => $event->slug]), [
            'players' => [
                ['email' => $captain->email, 'faction_id' => $ultramarines->id],
                ['email' => 'tarik@example.com', 'faction_id' => $sonsOfHorus->id],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('allegiance');
});
