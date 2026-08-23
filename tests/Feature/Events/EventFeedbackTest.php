<?php

use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\FeedbackInvitation;
use App\Models\FeedbackQuestion;
use App\Models\FeedbackResponse;
use App\Models\User;
use App\Notifications\Events\FeedbackRequestNotification;
use Database\Seeders\FeedbackQuestionSeeder;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * A completed Event with two Players and the question set seeded.
 *
 * @return array{0: Event, 1: User, 2: User, 3: User}
 */
function feedbackEvent(): array
{
    test()->seed(FeedbackQuestionSeeder::class);

    $event = Event::factory()->completed()->create();
    $organiser = organiserOf($event);

    $first = User::factory()->create();
    $second = User::factory()->create();

    EventAttendee::factory()->for($event)->withMember($first)->create();
    EventAttendee::factory()->for($event)->withMember($second)->create();

    return [$event, $organiser, $first, $second];
}

test('one organiser action emails every player a unique link', function () {
    Notification::fake();

    [$event, $organiser, $first, $second] = feedbackEvent();

    $this->actingAs($organiser)
        ->postJson(route('events.feedback.invite', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertJsonPath('data.invited', 2);

    expect(FeedbackInvitation::query()->where('event_id', $event->id)->count())->toBe(2);

    $tokens = [];

    foreach ([$first, $second] as $player) {
        Notification::assertSentTo($player, FeedbackRequestNotification::class, function (FeedbackRequestNotification $notification) use (&$tokens): bool {
            $tokens[] = $notification->plainToken;

            return $notification->via($notification) === ['mail'];
        });
    }

    expect($tokens)->toHaveCount(2)
        ->and(array_unique($tokens))->toHaveCount(2);
});

test('a player opens their link, submits once, and is refused a second time', function () {
    Notification::fake();

    [$event, $organiser, $first] = feedbackEvent();

    $plainToken = null;

    $this->actingAs($organiser)
        ->postJson(route('events.feedback.invite', ['event' => $event->slug]))
        ->assertSuccessful();

    Notification::assertSentTo($first, FeedbackRequestNotification::class, function (FeedbackRequestNotification $notification) use (&$plainToken): bool {
        $plainToken = $notification->plainToken;

        return true;
    });

    $form = $this->getJson(route('feedback.show', ['token' => $plainToken]))
        ->assertSuccessful()
        ->json('data');

    expect($form['event']['name'])->toBe($event->name)
        ->and($form['questions'])->toHaveCount(10);

    $rating = collect($form['questions'])->firstWhere('type', 'rating');
    $text = collect($form['questions'])->firstWhere('type', 'text');

    $payload = ['answers' => [
        ['question_id' => $rating['id'], 'rating' => 5],
        ['question_id' => $text['id'], 'answer' => 'The missions were excellent.'],
    ]];

    $this->postJson(route('feedback.store', ['token' => $plainToken]), $payload)
        ->assertSuccessful();

    expect(FeedbackResponse::query()->where('event_id', $event->id)->count())->toBe(2);

    $this->postJson(route('feedback.store', ['token' => $plainToken]), $payload)
        ->assertNotFound();

    expect(FeedbackResponse::query()->where('event_id', $event->id)->count())->toBe(2);
});

test('a response cannot be traced back to the player who wrote it', function () {
    $response = FeedbackResponse::factory()->create();

    expect(array_keys($response->getAttributes()))
        ->not->toContain('user_id')
        ->not->toContain('feedback_invitation_id');

    expect(Schema::getColumnListing('feedback_responses'))
        ->not->toContain('user_id')
        ->not->toContain('feedback_invitation_id');
});

test('a token expires thirty days after it is sent', function () {
    [$event, $organiser, $first] = feedbackEvent();

    $plainToken = Str::random(64);

    $invitation = FeedbackInvitation::factory()->for($event)->for($first)->expired()->create([
        'token' => FeedbackInvitation::hashToken($plainToken),
    ]);

    expect($invitation->sent_at->diffInDays($invitation->expires_at))->toBe(30.0);

    $this->getJson(route('feedback.show', ['token' => $plainToken]))
        ->assertNotFound();

    $this->postJson(route('feedback.store', ['token' => $plainToken]), ['answers' => []])
        ->assertNotFound();
});

test('feedback email ignores notification preferences', function () {
    $player = User::factory()->create(['notification_settings' => [
        'event_messages' => [],
        'result_activity' => [],
    ]]);

    $notification = new FeedbackRequestNotification(Event::factory()->create(), 'plain-token');

    expect($notification->via($player))->toEqual(['mail']);
});

test('organisers read a summarised dashboard and download a csv, neither identifying anyone', function () {
    Notification::fake();

    [$event, $organiser, $first, $second] = feedbackEvent();

    $this->actingAs($organiser)
        ->postJson(route('events.feedback.invite', ['event' => $event->slug]))
        ->assertSuccessful();

    $tokens = [];

    Notification::assertSentTo($first, FeedbackRequestNotification::class, function (FeedbackRequestNotification $notification) use (&$tokens): bool {
        $tokens[] = $notification->plainToken;

        return true;
    });

    $overall = FeedbackQuestion::query()->where('key', 'overall')->sole();
    $bestThing = FeedbackQuestion::query()->where('key', 'best_thing')->sole();

    $this->postJson(route('feedback.store', ['token' => $tokens[0]]), ['answers' => [
        ['question_id' => $overall->id, 'rating' => 4],
        ['question_id' => $bestThing->id, 'answer' => 'The terrain was superb.'],
    ]])->assertSuccessful();

    FeedbackResponse::factory()->for($event)->create(['feedback_question_id' => $overall->id, 'rating' => 2]);

    $dashboard = $this->actingAs($organiser)
        ->getJson(route('events.feedback.index', ['event' => $event->slug]))
        ->assertSuccessful();

    $questions = collect($dashboard->json('data.questions'))->keyBy('key');

    expect($dashboard->json('data.invitations.sent'))->toBe(2)
        ->and($dashboard->json('data.invitations.submitted'))->toBe(1)
        ->and($questions['overall']['response_count'])->toBe(2)
        ->and((float) $questions['overall']['average'])->toBe(3.0)
        ->and($questions['overall']['distribution']['4'])->toBe(1)
        ->and($questions['best_thing']['answers'])->toEqual(['The terrain was superb.'])
        ->and($dashboard->content())->not->toContain($first->name)
        ->and($dashboard->content())->not->toContain($first->email);

    $csv = $this->actingAs($organiser)
        ->get(route('events.feedback.export', ['event' => $event->slug]))
        ->assertSuccessful()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $body = $csv->streamedContent();

    expect($body)->toContain('question_key,prompt,type,rating,answer')
        ->and($body)->toContain('The terrain was superb.')
        ->and($body)->not->toContain($first->name)
        ->and($body)->not->toContain($first->email)
        ->and($body)->not->toContain('user_id');
});

test('players cannot read the feedback dashboard or export', function () {
    [$event, $organiser, $first] = feedbackEvent();

    $this->actingAs($first)
        ->getJson(route('events.feedback.index', ['event' => $event->slug]))
        ->assertForbidden();

    $this->actingAs($first)
        ->get(route('events.feedback.export', ['event' => $event->slug]))
        ->assertForbidden();
});
