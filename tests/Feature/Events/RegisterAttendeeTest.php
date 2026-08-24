<?php

use App\Actions\Events\EnrolPlayer;
use App\Actions\Events\RegisterAttendee;
use App\Actions\Events\SendEventInvite;
use App\Enums\Allegiance;
use App\Enums\EventInviteRole;
use App\Enums\EventOrganiserRole;
use App\Enums\RegistrationMode;
use App\Exceptions\EventIsFull;
use App\Models\Event;
use App\Models\EventAttendee;
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

test('an event with every place taken refuses another team', function () {
    [$event, $faction] = doublesEvent(['attendee_size' => 1, 'max_attendees' => 1]);
    EventAttendee::factory()->for($event)->create();

    $captain = User::factory()->create();

    $this->actingAs($captain)
        ->postJson(route('events.attendees.store', ['event' => $event->slug]), [
            'allegiance' => 'loyalist',
            'players' => [['email' => $captain->email, 'faction_id' => $faction->id]],
        ])
        ->assertForbidden();

    expect($event->attendees()->count())->toBe(1);
});

test('the registration transaction refuses a place that went while it was in flight', function () {
    [$event, $faction] = doublesEvent(['attendee_size' => 1, 'max_attendees' => 1]);
    EventAttendee::factory()->for($event)->create();

    // Straight at the action: the policy already said yes on an Event that
    // had a place, and this guard is what stands between that answer and an
    // over-full Event that cannot be paired.
    $register = fn () => app(RegisterAttendee::class)->handle(
        event: $event,
        players: [['email' => 'late@example.com', 'faction_id' => $faction->id]],
        registeredBy: User::factory()->create(),
    );

    expect($register)->toThrow(EventIsFull::class);
    expect($event->attendees()->count())->toBe(1);
});

test('a place lost mid-registration is reported as a conflict', function () {
    [$event, $faction] = doublesEvent(['attendee_size' => 1, 'max_attendees' => 2]);
    $captain = User::factory()->create();

    $this->instance(RegisterAttendee::class, new class (app(EnrolPlayer::class)) extends RegisterAttendee
    {
        public function handle(Event $event, array $players, User $registeredBy, ?string $name = null, ?Allegiance $allegiance = null): EventAttendee
        {
            throw EventIsFull::for($event);
        }
    });

    $this->actingAs($captain)
        ->postJson(route('events.attendees.store', ['event' => $event->slug]), [
            'allegiance' => 'loyalist',
            'players' => [['email' => $captain->email, 'faction_id' => $faction->id]],
        ])
        ->assertStatus(409)
        ->assertJsonPath('message', "{$event->name} is full. Ask an organiser whether there is a waiting list.");
});

test('an organiser is never shut out of a full event', function () {
    [$event, $faction] = doublesEvent(['attendee_size' => 1, 'max_attendees' => 1]);
    EventAttendee::factory()->for($event)->create();

    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Lead->value]);

    $this->actingAs($organiser)
        ->postJson(route('events.attendees.store', ['event' => $event->slug]), [
            'allegiance' => 'loyalist',
            'players' => [['email' => 'straggler@example.com', 'faction_id' => $faction->id]],
        ])
        ->assertCreated();

    expect($event->attendees()->count())->toBe(2);
});

test('an event with no limit is never full', function () {
    [$event, $faction] = doublesEvent(['attendee_size' => 1, 'max_attendees' => null]);
    EventAttendee::factory()->count(5)->for($event)->create();

    $captain = User::factory()->create();

    $this->actingAs($captain)
        ->postJson(route('events.attendees.store', ['event' => $event->slug]), [
            'allegiance' => 'loyalist',
            'players' => [['email' => $captain->email, 'faction_id' => $faction->id]],
        ])
        ->assertCreated();
});

test('the event says how full it is, so a captain knows before they start', function () {
    [$event] = doublesEvent(['attendee_size' => 1, 'max_attendees' => 2]);
    EventAttendee::factory()->for($event)->create();

    $this->getJson(route('events.show', ['event' => $event->slug]))
        ->assertOk()
        ->assertJsonPath('data.attendees_count', 1)
        ->assertJsonPath('data.max_attendees', 2)
        ->assertJsonPath('data.is_full', false);

    EventAttendee::factory()->for($event)->create();

    $this->getJson(route('events.show', ['event' => $event->slug]))
        ->assertJsonPath('data.is_full', true);
});

test('a captain looking at a full event is told they may not register', function () {
    [$event] = doublesEvent(['attendee_size' => 1, 'max_attendees' => 1]);
    EventAttendee::factory()->for($event)->create();

    $this->actingAs(User::factory()->create())
        ->getJson(route('events.show', ['event' => $event->slug]))
        ->assertOk()
        ->assertJsonPath('data.viewer.permissions.register', false);
});
