<?php

use App\Actions\Events\CreateEvent;
use App\Actions\Events\ReplaceEventScoreTypes;
use App\Enums\EventOrganiserRole;
use App\Enums\EventStatus;
use App\Enums\PairingFormat;
use App\Models\Event;
use App\Models\GameSystem;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * A complete wizard submission, overridable field by field.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function newEvent(array $overrides = []): array
{
    return [
        'name' => 'Croydon Doubles',
        'slug' => 'croydon-doubles',
        'starts_at' => now()->addMonth()->setTime(9, 0)->toIso8601String(),
        'ends_at' => now()->addMonth()->setTime(18, 0)->toIso8601String(),
        'timezone' => 'Europe/London',
        'game_system_id' => GameSystem::factory()->create()->id,
        'attendee_size' => 2,
        'max_attendees' => 24,
        'venue_name' => 'The Hall',
        'venue_city' => 'Croydon',
        'venue_country' => 'GB',
        'pairing_format' => 'swiss',
        'settings' => [
            'round_count' => 5,
            'requires_opposed_allegiance' => true,
        ],
        'score_types' => [
            [
                'name' => 'Match Points',
                'abbreviation' => 'MP',
                'sort_direction' => 'desc',
                'is_derived' => true,
                'is_primary' => true,
                'counts_for_ranking' => true,
                'win_points' => 3,
                'draw_points' => 1,
                'loss_points' => 0,
            ],
            [
                'name' => 'Victory Points',
                'abbreviation' => 'VP',
                'sort_direction' => 'desc',
                'is_derived' => false,
                'is_primary' => false,
                'counts_for_ranking' => true,
                'win_points' => null,
                'draw_points' => null,
                'loss_points' => null,
            ],
        ],
        ...$overrides,
    ];
}

test('an organiser creates a draft event that they lead, scored on the columns they chose', function () {
    $creator = User::factory()->create();

    $this->actingAs($creator)
        ->postJson(route('events.store'), newEvent())
        ->assertCreated()
        ->assertJsonPath('data.slug', 'croydon-doubles')
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.viewer.is_lead_organiser', true);

    $event = Event::query()->where('slug', 'croydon-doubles')->sole();

    expect($event->status)->toBe(EventStatus::Draft)
        ->and($event->timezone)->toBe('Europe/London')
        ->and($event->attendee_size)->toBe(2)
        ->and($event->organisers()->wherePivot('role', EventOrganiserRole::Lead->value)->pluck('users.id')->all())
        ->toBe([$creator->id])
        ->and($event->scoreTypes()->orderBy('display_order')->pluck('slug')->all())
        ->toBe(['match-points', 'victory-points']);
});

test('an account nobody has claimed cannot create an event, since it would expire and leave the event leaderless', function () {
    $this->actingAs(User::factory()->unclaimed()->create())
        ->postJson(route('events.store'), newEvent())
        ->assertForbidden();

    expect(Event::query()->count())->toBe(0);
});

test('a guest cannot create an event', function () {
    $this->postJson(route('events.store'), newEvent())->assertUnauthorized();
});

test('an event is always created as a draft, and a caller asking for another status is told so', function () {
    $this->actingAs(User::factory()->create())
        ->postJson(route('events.store'), newEvent(['status' => 'published']))
        ->assertJsonValidationErrors(['status']);

    expect(Event::query()->count())->toBe(0);
});

test('an event scored on nothing is refused, and nothing is created', function () {
    $this->actingAs(User::factory()->create())
        ->postJson(route('events.store'), newEvent(['score_types' => []]))
        ->assertJsonValidationErrors(['score_types']);

    expect(Event::query()->count())->toBe(0);
});

test('the scoring set is held to the same rules as replacing it', function () {
    $submission = newEvent();
    $submission['score_types'][1]['is_primary'] = true;
    $submission['score_types'][0]['win_points'] = null;

    $this->actingAs(User::factory()->create())
        ->postJson(route('events.store'), $submission)
        ->assertJsonValidationErrors(['score_types.1.is_primary']);

    $submission['score_types'][1]['is_primary'] = false;

    $this->actingAs(User::factory()->create())
        ->postJson(route('events.store'), $submission)
        ->assertJsonValidationErrors(['score_types.0.win_points']);
});

test('a column cannot claim an id, because the event it would belong to does not exist yet', function () {
    $submission = newEvent();
    $submission['score_types'][0]['id'] = 1;

    $this->actingAs(User::factory()->create())
        ->postJson(route('events.store'), $submission)
        ->assertJsonValidationErrors(['score_types.0.id']);
});

test('an event cannot start in the past or end before it starts', function () {
    $creator = User::factory()->create();

    $this->actingAs($creator)
        ->postJson(route('events.store'), newEvent(['starts_at' => now()->subDay()->toIso8601String()]))
        ->assertJsonValidationErrors(['starts_at' => 'cannot start in the past']);

    $this->actingAs($creator)
        ->postJson(route('events.store'), newEvent([
            'starts_at' => now()->addMonth()->setTime(18, 0)->toIso8601String(),
            'ends_at' => now()->addMonth()->setTime(9, 0)->toIso8601String(),
        ]))
        ->assertJsonValidationErrors(['ends_at' => 'cannot end before it starts']);
});

test('the url is lowercase letters, numbers and hyphens, and not a reserved word', function (string $slug, string $reason) {
    $this->actingAs(User::factory()->create())
        ->postJson(route('events.store'), newEvent(['slug' => $slug]))
        ->assertJsonValidationErrors(['slug' => $reason]);
})->with('refused event slugs');

test('nobody may hold a url another event already has, even a draft', function () {
    Event::factory()->draft()->create(['slug' => 'taken-already']);

    $this->actingAs(User::factory()->create())
        ->postJson(route('events.store'), newEvent(['slug' => 'taken-already']))
        ->assertJsonValidationErrors(['slug' => 'already taken']);
});

test('a slug taken between validation and the write is refused as taken, not a server error', function () {
    Event::factory()->create(['slug' => 'croydon-doubles']);

    $submission = newEvent();

    expect(fn () => app(CreateEvent::class)->execute(
        User::factory()->create(),
        Arr::except($submission, ['score_types', 'settings']),
        $submission['score_types'],
    ))->toThrow(ValidationException::class, 'That URL is already taken.');

    expect(Event::query()->count())->toBe(1);
});

test('a worked-out column sent with a numeric flag still needs its points', function (mixed $flag) {
    $submission = newEvent();
    $submission['score_types'][0]['is_derived'] = $flag;
    $submission['score_types'][0]['win_points'] = null;

    $this->actingAs(User::factory()->create())
        ->postJson(route('events.store'), $submission)
        ->assertJsonValidationErrors(['score_types.0.win_points']);
})->with([1, '1']);

test('the timezone must be one the venue could keep', function () {
    $this->actingAs(User::factory()->create())
        ->postJson(route('events.store'), newEvent(['timezone' => 'Croydon/High Street']))
        ->assertJsonValidationErrors(['timezone']);
});

test('nothing is left behind when the scoring cannot be written', function () {
    $this->mock(ReplaceEventScoreTypes::class)
        ->shouldReceive('execute')
        ->andThrow(new RuntimeException('The scoring could not be written.'));

    $this->withoutExceptionHandling();

    expect(fn () => $this->actingAs(User::factory()->create())
        ->postJson(route('events.store'), newEvent()))
        ->toThrow(RuntimeException::class);

    expect(Event::query()->count())->toBe(0)
        ->and(DB::table('event_organisers')->count())->toBe(0);
});

test('the event is played the way the organiser chose: swiss, over the rounds they set, with players picking a side', function () {
    $this->actingAs(User::factory()->create())
        ->postJson(route('events.store'), newEvent())
        ->assertCreated()
        ->assertJsonPath('data.pairing_format', 'swiss')
        ->assertJsonPath('data.requires_allegiance', true);

    $event = Event::query()->sole();

    expect($event->pairing_format)->toBe(PairingFormat::Swiss)
        ->and($event->settings->roundCount)->toBe(5)
        ->and($event->settings->requiresOpposedAllegiance)->toBeTrue();
});

test('a format that is not built yet is refused, rather than quietly paired as swiss', function (string $format) {
    $this->actingAs(User::factory()->create())
        ->postJson(route('events.store'), newEvent(['pairing_format' => $format]))
        ->assertJsonValidationErrors(['pairing_format' => 'pairing format that is available']);

    expect(Event::query()->count())->toBe(0);
})->with(['round_robin', 'knockout', 'random', 'chess']);

test('an event must say how it is paired', function () {
    $this->actingAs(User::factory()->create())
        ->postJson(route('events.store'), Arr::except(newEvent(), 'pairing_format'))
        ->assertJsonValidationErrors(['pairing_format']);
});

test('an event with no round count runs open-ended, and players need not pick a side unless asked', function () {
    $this->actingAs(User::factory()->create())
        ->postJson(route('events.store'), newEvent(['settings' => ['round_count' => null]]))
        ->assertCreated();

    $settings = Event::query()->sole()->settings;

    expect($settings->roundCount)->toBeNull()
        ->and($settings->requiresOpposedAllegiance)->toBeFalse();
});

test('an event sent with no settings at all is open-ended too', function () {
    $this->actingAs(User::factory()->create())
        ->postJson(route('events.store'), Arr::except(newEvent(), 'settings'))
        ->assertCreated();

    expect(Event::query()->sole()->settings->roundCount)->toBeNull();
});

test('a round count is a positive whole number', function (mixed $roundCount) {
    $this->actingAs(User::factory()->create())
        ->postJson(route('events.store'), newEvent(['settings' => ['round_count' => $roundCount]]))
        ->assertJsonValidationErrors(['settings.round_count' => 'whole number of rounds']);
})->with([0, -1, 2.5, 'five']);

test('a setting the wizard does not ask for is refused rather than stored', function () {
    $this->actingAs(User::factory()->create())
        ->postJson(route('events.store'), newEvent(['settings' => ['standings_visible' => true]]))
        ->assertJsonValidationErrors(['settings']);

    expect(Event::query()->count())->toBe(0);
});
