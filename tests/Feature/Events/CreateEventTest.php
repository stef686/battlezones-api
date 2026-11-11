<?php

use App\Actions\Events\CreateEvent;
use App\Actions\Events\ReplaceEventScoreTypes;
use App\Enums\EventOrganiserRole;
use App\Enums\EventStatus;
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

test('the url is lowercase letters, numbers and hyphens, and nobody else may hold it', function (string $slug, string $reason) {
    Event::factory()->create(['slug' => 'taken-already']);

    $this->actingAs(User::factory()->create())
        ->postJson(route('events.store'), newEvent(['slug' => $slug]))
        ->assertJsonValidationErrors(['slug' => $reason]);
})->with([
    'upper case' => ['Croydon', 'lowercase letters, numbers and single hyphens'],
    'spaces' => ['croydon doubles', 'lowercase letters, numbers and single hyphens'],
    'leading hyphen' => ['-croydon', 'lowercase letters, numbers and single hyphens'],
    'trailing hyphen' => ['croydon-', 'lowercase letters, numbers and single hyphens'],
    'doubled hyphen' => ['croydon--doubles', 'lowercase letters, numbers and single hyphens'],
    'only hyphens' => ['---', 'lowercase letters, numbers and single hyphens'],
    'too short' => ['cd', 'between 3 and 60'],
    'taken' => ['taken-already', 'already taken'],
    'reserved by the create wizard' => ['new', 'reserved'],
]);

test('a slug taken between validation and the write is refused as taken, not a server error', function () {
    Event::factory()->create(['slug' => 'croydon-doubles']);

    $submission = newEvent();

    expect(fn () => app(CreateEvent::class)->execute(
        User::factory()->create(),
        Arr::except($submission, 'score_types'),
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
