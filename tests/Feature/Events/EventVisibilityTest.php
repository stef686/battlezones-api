<?php

use App\Enums\EventOrganiserRole;
use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Who is looking at the Event, built against it.
 *
 * @return array<string, Closure(Event): ?User>
 */
function eventViewers(): array
{
    return [
        'a guest' => fn (Event $event): ?User => null,
        'an unrelated User' => fn (Event $event): ?User => User::factory()->create(),
        'one of its Players' => function (Event $event): User {
            $player = User::factory()->create();
            EventAttendee::factory()->for($event)->withMember($player)->create();

            return $player;
        },
        'a co-Organiser' => function (Event $event): User {
            $colleague = User::factory()->create();
            $event->organisers()->attach($colleague, ['role' => EventOrganiserRole::Organiser->value]);

            return $colleague;
        },
        'its Lead Organiser' => fn (Event $event): User => organiserOf($event),
    ];
}

dataset('event visibility', function (): Generator {
    $public = [EventStatus::Published, EventStatus::Active, EventStatus::Completed];
    $organises = ['a co-Organiser', 'its Lead Organiser'];

    foreach (EventStatus::cases() as $status) {
        foreach (array_keys(eventViewers()) as $viewer) {
            $visible = in_array($status, $public, true) || in_array($viewer, $organises, true);

            yield "{$status->value} to {$viewer}" => [$status, $viewer, $visible];
        }
    }
});

test('an Event is visible when it is public or the viewer organises it', function (EventStatus $status, string $viewer, bool $visible) {
    $event = Event::factory()->create(['status' => $status]);
    $user = eventViewers()[$viewer]($event);

    expect($event->fresh()->isVisibleTo($user))->toBe($visible);
})->with('event visibility');

test('a public Event is visible without asking who organises it', function () {
    $event = Event::factory()->published()->create();
    $stranger = User::factory()->create();

    DB::enableQueryLog();

    expect($event->isVisibleTo($stranger))->toBeTrue()
        ->and(DB::getQueryLog())->toBeEmpty();
});
