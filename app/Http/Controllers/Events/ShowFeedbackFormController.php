<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\FeedbackInvitation;
use App\Models\FeedbackQuestion;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
class ShowFeedbackFormController extends Controller
{
    #[Endpoint('Show the Feedback Form', 'The questions behind a feedback link. A spent or expired link is not found rather than explained, since the token is the only credential.')]
    #[UrlParam('token', 'string', 'The token from the feedback email.', example: 'aVeryLongRandomToken')]
    #[Response(['data' => [
        'event' => ['id' => 1, 'name' => 'London Grand Tournament', 'slug' => 'london-grand-tournament'],
        'expires_at' => '2026-09-20T09:00:00+00:00',
        'questions' => [[
            'id' => 1,
            'key' => 'overall',
            'prompt' => 'How was the Event overall?',
            'type' => 'rating',
        ]],
    ]])]
    #[Response(['message' => 'Not Found.'], 404, 'The link is unknown, already used, or expired.')]
    public function __invoke(string $token): JsonResponse
    {
        $invitation = FeedbackInvitation::findByToken($token);

        abort_unless($invitation instanceof FeedbackInvitation && $invitation->isUsable(), 404);

        $questions = FeedbackQuestion::query()->orderBy('display_order')->orderBy('id')->get();

        return response()->json(['data' => [
            'event' => [
                'id' => $invitation->event->id,
                'name' => $invitation->event->name,
                'slug' => $invitation->event->slug,
            ],
            'expires_at' => $invitation->expires_at->toIso8601String(),
            'questions' => $questions->map(fn (FeedbackQuestion $question): array => [
                'id' => $question->id,
                'key' => $question->key,
                'prompt' => $question->prompt,
                'type' => $question->type->value,
            ])->all(),
        ]]);
    }
}
