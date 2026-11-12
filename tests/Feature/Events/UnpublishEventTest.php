<?php

use App\Actions\Events\UnpublishEvent;
use App\Enums\EventInviteRole;
use App\Enums\EventOrganiserRole;
use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventInvite;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * Somebody into this Event by one of the ways that holds it published.
 */
function letSomebodyInto(Event $event, string $way): void
{
    match ($way) {
        'entry' => EventAttendee::factory()->for($event)->create(),
        'invite' => inviteTo($event),
    };
}

/**
 * An Invite to this Event, as SendEventInvite would leave it.
 *
 * @param  array<string, mixed>  $attributes
 */
function inviteTo(Event $event, array $attributes = []): EventInvite
{
    return EventInvite::query()->create([
        'event_id' => $event->getKey(),
        'user_id' => User::factory()->create()->getKey(),
        'role' => EventInviteRole::Captain,
        'token' => EventInvite::hashToken(Str::random(40)),
        'expires_at' => now()->addWeek(),
        ...$attributes,
    ]);
}

test('the lead organiser takes a published event back to draft, and gets the event back as they now see it', function () {
    $event = Event::factory()->published()->create();

    $this->actingAs(organiserOf($event))
        ->deleteJson(route('events.unpublish', $event))
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.viewer.is_lead_organiser', true);

    expect($event->refresh()->status)->toBe(EventStatus::Draft);
});

test('a co-organiser cannot unpublish, since publishing was the lead\'s call', function () {
    $event = Event::factory()->published()->create();
    organiserOf($event);

    $this->actingAs(coOrganiserOf($event))
        ->deleteJson(route('events.unpublish', $event))
        ->assertForbidden();

    expect($event->refresh()->status)->toBe(EventStatus::Published);
});

test('a reader who does not run the event cannot unpublish it', function () {
    $event = Event::factory()->published()->create();

    $this->actingAs(User::factory()->create())
        ->deleteJson(route('events.unpublish', $event))
        ->assertForbidden();

    expect($event->refresh()->status)->toBe(EventStatus::Published);
});

test('anyone a cancelled event is hidden from finds nothing to unpublish', function () {
    $event = Event::factory()->cancelled()->create();

    $this->actingAs(User::factory()->create())
        ->deleteJson(route('events.unpublish', $event))
        ->assertNotFound();
});

test('a guest is asked to sign in', function () {
    $this->deleteJson(route('events.unpublish', Event::factory()->published()->create()))->assertUnauthorized();
});

test('an admin may unpublish, so the admin panel keeps its repair path', function () {
    $event = Event::factory()->published()->create();

    expect(Gate::forUser(User::factory()->admin()->create())->allows('unpublish', $event))->toBeTrue();
});

test('once anybody is in, the event cannot go back to draft', function (string $way) {
    $event = Event::factory()->published()->create();
    letSomebodyInto($event, $way);

    $this->actingAs(organiserOf($event))
        ->deleteJson(route('events.unpublish', $event))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status' => 'People have entered or been invited — cancel the event instead.']);

    expect($event->refresh()->status)->toBe(EventStatus::Published);
})->with([
    'an attendee has entered' => ['entry'],
    'an invite is outstanding' => ['invite'],
]);

test('an invite that can no longer let anybody in does not hold the event published', function (array $attributes) {
    $event = Event::factory()->published()->create();
    inviteTo($event, $attributes);

    $this->actingAs(organiserOf($event))
        ->deleteJson(route('events.unpublish', $event))
        ->assertSuccessful();

    expect($event->refresh()->status)->toBe(EventStatus::Draft);
})->with([
    'revoked' => [['revoked_at' => now()->subHour()]],
    'expired' => [['expires_at' => now()->subHour()]],
]);

test('only a published event can go back to draft', function (EventStatus $status) {
    $event = Event::factory()->create(['status' => $status]);

    $this->actingAs(organiserOf($event))
        ->deleteJson(route('events.unpublish', $event))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status' => 'Only a published event can be taken back to draft.']);

    expect($event->refresh()->status)->toBe($status);
})->with([EventStatus::Draft, EventStatus::Active, EventStatus::Completed, EventStatus::Cancelled]);

test('the event tells its lead whether it can go back to draft now', function (?string $way, EventStatus $status, bool $mayUnpublish) {
    $event = Event::factory()->create(['status' => $status]);
    $lead = organiserOf($event);

    if ($way !== null) {
        letSomebodyInto($event, $way);
    }

    $this->actingAs($lead)
        ->getJson(route('events.show', $event))
        ->assertJsonPath('data.viewer.permissions.unpublish', $mayUnpublish);
})->with([
    'nobody in' => [null, EventStatus::Published, true],
    'an attendee has entered' => ['entry', EventStatus::Published, false],
    'an invite is outstanding' => ['invite', EventStatus::Published, false],
    'it has begun' => [null, EventStatus::Active, false],
]);

test('a co-organiser is told they may not unpublish', function () {
    $event = Event::factory()->published()->create();
    $colleague = User::factory()->create();
    $event->organisers()->attach($colleague, ['role' => EventOrganiserRole::Organiser->value]);

    $this->actingAs($colleague)
        ->getJson(route('events.show', $event))
        ->assertJsonPath('data.viewer.permissions.unpublish', false);
});

test('an entry landing since the event was read is not unpublished over', function () {
    $event = Event::factory()->published()->create();
    $action = app(UnpublishEvent::class);

    expect($action->allows($event))->toBeTrue();

    // Somebody entered between this request checking and writing.
    EventAttendee::factory()->for($event)->create();

    expect(fn () => $action->execute($event))
        ->toThrow(ValidationException::class, 'People have entered or been invited — cancel the event instead.');

    expect($event->fresh()->status)->toBe(EventStatus::Published);
});
