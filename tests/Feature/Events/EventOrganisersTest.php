<?php

use App\Enums\EventOrganiserRole;
use App\Models\Event;
use App\Models\User;

test('a lead organiser can appoint another organiser', function () {
    $event = Event::factory()->published()->create();
    $lead = User::factory()->create();
    $event->organisers()->attach($lead, ['role' => EventOrganiserRole::Lead->value]);

    $colleague = User::factory()->create();

    $this->actingAs($lead)
        ->postJson(route('events.organisers.store', ['event' => $event->slug]), [
            'email' => $colleague->email,
        ])
        ->assertSuccessful();

    expect($event->fresh()->isOrganisedBy($colleague))->toBeTrue();
});

test('an organiser cannot appoint another organiser', function () {
    $event = Event::factory()->published()->create();
    $organiser = User::factory()->create();
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Organiser->value]);

    $this->actingAs($organiser)
        ->postJson(route('events.organisers.store', ['event' => $event->slug]), [
            'email' => User::factory()->create()->email,
        ])
        ->assertForbidden();
});

test('an organiser cannot remove the lead who appointed them', function () {
    $event = Event::factory()->published()->create();
    $lead = User::factory()->create();
    $organiser = User::factory()->create();
    $event->organisers()->attach($lead, ['role' => EventOrganiserRole::Lead->value]);
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Organiser->value]);

    $this->actingAs($organiser)
        ->deleteJson(route('events.organisers.destroy', ['event' => $event->slug, 'user' => $lead->id]))
        ->assertForbidden();

    expect($event->fresh()->isOrganisedBy($lead))->toBeTrue();
});

test('an unclaimed account cannot be appointed as an organiser', function () {
    $event = Event::factory()->published()->create();
    $lead = User::factory()->create();
    $event->organisers()->attach($lead, ['role' => EventOrganiserRole::Lead->value]);

    $invited = User::factory()->unclaimed()->create();

    $this->actingAs($lead)
        ->postJson(route('events.organisers.store', ['event' => $event->slug]), [
            'email' => $invited->email,
        ])
        ->assertUnprocessable();

    expect($event->fresh()->isOrganisedBy($invited))->toBeFalse();
});

test('an unknown email cannot be appointed as an organiser', function () {
    $event = Event::factory()->published()->create();
    $lead = User::factory()->create();
    $event->organisers()->attach($lead, ['role' => EventOrganiserRole::Lead->value]);

    $this->actingAs($lead)
        ->postJson(route('events.organisers.store', ['event' => $event->slug]), [
            'email' => 'nobody@example.com',
        ])
        ->assertUnprocessable();
});

test('a lead can remove an organiser', function () {
    $event = Event::factory()->published()->create();
    $lead = User::factory()->create();
    $organiser = User::factory()->create();
    $event->organisers()->attach($lead, ['role' => EventOrganiserRole::Lead->value]);
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Organiser->value]);

    $this->actingAs($lead)
        ->deleteJson(route('events.organisers.destroy', ['event' => $event->slug, 'user' => $organiser->id]))
        ->assertSuccessful();

    expect($event->fresh()->isOrganisedBy($organiser))->toBeFalse();
});

test('organisers are distinguished from leads and from everyone else', function () {
    $event = Event::factory()->published()->create();
    $lead = User::factory()->create();
    $organiser = User::factory()->create();
    $stranger = User::factory()->create();

    $event->organisers()->attach($lead, ['role' => EventOrganiserRole::Lead->value]);
    $event->organisers()->attach($organiser, ['role' => EventOrganiserRole::Organiser->value]);

    expect($event->isLedBy($lead))->toBeTrue()
        ->and($event->isOrganisedBy($lead))->toBeTrue()
        ->and($event->isLedBy($organiser))->toBeFalse()
        ->and($event->isOrganisedBy($organiser))->toBeTrue()
        ->and($event->isOrganisedBy($stranger))->toBeFalse();
});

test('organisers are listed to organisers only', function () {
    $event = Event::factory()->published()->create();
    $lead = User::factory()->create(['name' => 'Abaddon']);
    $event->organisers()->attach($lead, ['role' => EventOrganiserRole::Lead->value]);

    $this->actingAs($lead)
        ->getJson(route('events.organisers.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonPath('data.0.name', 'Abaddon')
        ->assertJsonPath('data.0.role', 'lead');

    $this->actingAs(User::factory()->create())
        ->getJson(route('events.organisers.index', ['event' => $event->slug]))
        ->assertForbidden();
});
