<?php

use App\Enums\ScheduleBlockType;
use App\Models\Event;
use App\Models\EventPoll;
use App\Models\EventScheduleBlock;
use App\Models\Round;
use Illuminate\Support\Collection;

/**
 * The schedule as a Player reads it, keyed by block label.
 *
 * @return Collection<string, array<string, mixed>>
 */
function scheduleBlocks(Event $event): Collection
{
    return collect(test()->getJson(route('events.schedule.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->json('data'))
        ->flatMap(fn (array $day): array => $day['blocks'])
        ->keyBy('label');
}

test('a round block is not live while its round is still a draft', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->create(['number' => 1]);

    EventScheduleBlock::factory()->for($event)->round($round->id)->create(['label' => 'Round 1']);

    $block = scheduleBlocks($event)['Round 1'];

    expect($block['target_state'])->toBeNull()
        ->and($block['target_id'])->toBe($round->id)
        ->and($block)->not->toHaveKey('url');
});

test('a round block flips live when its round publishes and back when it is unpublished', function () {
    $event = Event::factory()->active()->create();
    $organiser = organiserOf($event);
    $round = Round::factory()->for($event)->create(['number' => 1]);

    EventScheduleBlock::factory()->for($event)->round($round->id)->create(['label' => 'Round 1']);

    $this->actingAs($organiser)
        ->postJson(route('events.rounds.publish', ['event' => $event->slug, 'round' => $round->id]))
        ->assertSuccessful();

    expect(scheduleBlocks($event)['Round 1']['target_state'])->toBe('live');

    $this->actingAs($organiser)
        ->deleteJson(route('events.rounds.unpublish', ['event' => $event->slug, 'round' => $round->id]))
        ->assertSuccessful();

    expect(scheduleBlocks($event)['Round 1']['target_state'])->toBeNull();
});

test('a painting block is live only while the poll window is open', function () {
    $event = Event::factory()->active()->create();
    $organiser = organiserOf($event);
    $poll = EventPoll::factory()->for($event)->create();

    EventScheduleBlock::factory()->for($event)->paintingVoting()->create(['label' => 'Painting Voting']);

    expect(scheduleBlocks($event)['Painting Voting']['target_state'])->toBeNull();

    $this->actingAs($organiser)
        ->postJson(route('events.polls.open', ['event' => $event->slug, 'poll' => $poll->id]))
        ->assertSuccessful();

    $live = scheduleBlocks($event)['Painting Voting'];

    expect($live['target_state'])->toBe('live')
        ->and($live['target_id'])->toBe($poll->id);

    $this->actingAs($organiser)
        ->postJson(route('events.polls.close', ['event' => $event->slug, 'poll' => $poll->id]))
        ->assertSuccessful();

    // A closed window is finished rather than nowhere: it happened, and the
    // schedule says so.
    expect(scheduleBlocks($event)['Painting Voting']['target_state'])->toBe('finished');
});

test('an info block is never live and carries no target', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create(['number' => 1]);
    EventPoll::factory()->for($event)->open()->create();

    EventScheduleBlock::factory()->for($event)->create(['label' => 'Lunch']);

    $block = scheduleBlocks($event)['Lunch'];

    expect($block['type'])->toBe(ScheduleBlockType::Info->value)
        ->and($block['target_state'])->toBeNull()
        ->and($block['target_id'])->toBeNull()
        ->and($round->isLive())->toBeTrue();
});

test('the schedule response carries no urls', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create(['number' => 1]);
    EventScheduleBlock::factory()->for($event)->round($round->id)->create(['label' => 'Round 1']);

    $body = $this->getJson(route('events.schedule.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->content();

    expect($body)->not->toContain('http')
        ->and($body)->not->toContain('/rounds/');
});

test('only the round being played is live, and the ones behind it are finished', function () {
    $event = Event::factory()->active()->create();
    $first = Round::factory()->for($event)->live()->create(['number' => 1]);
    $second = Round::factory()->for($event)->live()->create(['number' => 2]);

    EventScheduleBlock::factory()->for($event)->round($first->id)->create(['label' => 'Round 1']);
    EventScheduleBlock::factory()->for($event)->round($second->id)->create(['label' => 'Round 2']);

    $blocks = scheduleBlocks($event);

    // Live is a latch, so Round 1 is still `live` in its own right. What the
    // schedule reports is where the Event has got to, not where each Round has.
    expect($blocks['Round 1']['target_state'])->toBe('finished')
        ->and($blocks['Round 2']['target_state'])->toBe('live')
        ->and($first->isLive())->toBeTrue();
});

test('nothing is still happening once the event is over', function () {
    $event = Event::factory()->completed()->create();
    $round = Round::factory()->for($event)->live()->create(['number' => 1]);

    EventScheduleBlock::factory()->for($event)->round($round->id)->create(['label' => 'Round 1']);

    expect(scheduleBlocks($event)['Round 1']['target_state'])->toBe('finished');
});
