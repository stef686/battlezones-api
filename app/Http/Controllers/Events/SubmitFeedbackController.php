<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\SubmitFeedbackRequest;
use App\Models\FeedbackInvitation;
use App\Models\FeedbackResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
class SubmitFeedbackController extends Controller
{
    #[Endpoint('Submit Feedback', 'Answers are stored against the Event and the question only — never against the Player. The link is spent afterwards, which is the sole reason the invitation records who it belonged to.')]
    #[UrlParam('token', 'string', 'The token from the feedback email.', example: 'aVeryLongRandomToken')]
    public function __invoke(SubmitFeedbackRequest $request, string $token): JsonResponse
    {
        $invitation = FeedbackInvitation::findByToken($token);

        abort_unless($invitation instanceof FeedbackInvitation && $invitation->isUsable(), 404);

        DB::transaction(function () use ($invitation, $request): void {
            foreach ($request->answers() as $answer) {
                FeedbackResponse::query()->create([
                    'event_id' => $invitation->event_id,
                    'feedback_question_id' => $answer['question_id'],
                    'rating' => $answer['rating'],
                    'answer' => $answer['answer'],
                ]);
            }

            $invitation->forceFill(['submitted_at' => now()])->save();
        });

        return response()->json(['data' => ['submitted' => true]]);
    }
}
