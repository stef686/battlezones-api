<?php

use App\Enums\EventOrganiserRole;
use App\Enums\EventStatus;
use App\Enums\RegistrationMode;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * A Draft with everything publishing asks of it.
 *
 * @param  array<string, mixed>  $attributes
 */
function publishableDraft(array $attributes = []): Event
{
    return Event::factory()->draft()->create([
        'venue_name' => 'The Hall',
        'venue_city' => 'Croydon',
        'starts_at' => now()->addMonth(),
        'ends_at' => now()->addMonth()->addHours(9),
        ...$attributes,
    ]);
}

test('the lead organiser publishes their draft, and gets the event back as they now see it', function () {
    $event = publishableDraft();

    $this->actingAs(organiserOf($event))
        ->postJson(route('events.publish', $event))
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'published')
        ->assertJsonPath('data.viewer.is_lead_organiser', true);

    expect($event->refresh()->status)->toBe(EventStatus::Published);
});

test('a co-organiser cannot publish, since putting the event in front of players is the lead\'s call', function () {
    $event = publishableDraft();
    organiserOf($event);
    $colleague = User::factory()->create();
    $event->organisers()->attach($colleague, ['role' => EventOrganiserRole::Organiser->value]);

    $this->actingAs($colleague)
        ->postJson(route('events.publish', $event))
        ->assertForbidden();

    expect($event->refresh()->status)->toBe(EventStatus::Draft);
});

test('anyone the draft is hidden from finds nothing to publish', function (bool $isPlayer) {
    $event = publishableDraft();
    $reader = User::factory()->create();

    if ($isPlayer) {
        EventAttendee::factory()->for($event)->withMember($reader)->create();
    }

    $this->actingAs($reader)
        ->postJson(route('events.publish', $event))
        ->assertNotFound();

    expect($event->refresh()->status)->toBe(EventStatus::Draft);
})->with([
    'one of its players' => [true],
    'an unrelated user' => [false],
]);

test('a guest is asked to sign in', function () {
    $this->postJson(route('events.publish', publishableDraft()))->assertUnauthorized();
});

test('an admin may publish, so the admin panel keeps its repair path', function () {
    $event = publishableDraft();

    expect(Gate::forUser(User::factory()->admin()->create())->allows('publish', $event))->toBeTrue();
});

test('each thing publishing needs is refused under the field that fixes it', function (array $attributes, string $field) {
    $event = publishableDraft($attributes);

    $this->actingAs(organiserOf($event))
        ->postJson(route('events.publish', $event))
        ->assertUnprocessable()
        ->assertOnlyJsonValidationErrors([$field]);

    expect($event->refresh()->status)->toBe(EventStatus::Draft);
})->with([
    'no venue name' => [['venue_name' => null], 'venue_name'],
    'a blank venue name' => [['venue_name' => '  '], 'venue_name'],
    'no venue city' => [['venue_city' => null], 'venue_city'],
    'an event already over' => [['starts_at' => now()->subDays(2), 'ends_at' => now()->subDay()], 'ends_at'],
]);

test('everything standing in the way is named from one attempt', function () {
    $event = publishableDraft(['venue_name' => null, 'venue_city' => null]);

    $this->actingAs(organiserOf($event))
        ->postJson(route('events.publish', $event))
        ->assertUnprocessable()
        ->assertOnlyJsonValidationErrors(['venue_name', 'venue_city']);
});

test('only a draft can be published', function (EventStatus $status) {
    $event = publishableDraft(['status' => $status]);

    $this->actingAs(organiserOf($event))
        ->postJson(route('events.publish', $event))
        ->assertUnprocessable()
        ->assertOnlyJsonValidationErrors(['status']);

    expect($event->refresh()->status)->toBe($status);
})->with([EventStatus::Published, EventStatus::Active, EventStatus::Completed, EventStatus::Cancelled]);

test('once published, players can find the event and enter it', function () {
    $event = publishableDraft(['registration_mode' => RegistrationMode::Open, 'registration_closes_at' => null]);

    $this->actingAs(organiserOf($event))
        ->postJson(route('events.publish', $event))
        ->assertSuccessful();

    $this->getJson(route('events.index'))
        ->assertSuccessful()
        ->assertJsonPath('data.0.slug', $event->slug);

    $this->actingAs(User::factory()->create())
        ->getJson(route('events.show', $event))
        ->assertSuccessful()
        ->assertJsonPath('data.viewer.permissions.register', true);
});

test('the event tells its reader whether they may publish it', function (EventOrganiserRole $role, bool $mayPublish) {
    $event = publishableDraft();
    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => $role->value]);

    $this->actingAs($organiser)
        ->getJson(route('events.show', $event))
        ->assertJsonPath('data.viewer.permissions.publish', $mayPublish);
})->with([
    'the lead' => [EventOrganiserRole::Lead, true],
    'a co-organiser' => [EventOrganiserRole::Organiser, false],
]);
