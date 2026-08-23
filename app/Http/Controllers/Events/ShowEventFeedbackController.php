<?php

namespace App\Http\Controllers\Events;

use App\Enums\FeedbackQuestionType;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\FeedbackInvitation;
use App\Models\FeedbackQuestion;
use App\Models\FeedbackResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class ShowEventFeedbackController extends Controller
{
    #[Endpoint('Read Feedback', 'Organisers only. Ratings summarised, free text listed, and nothing tying either to a Player — the responses carry no such link to begin with.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    public function __invoke(Event $event): JsonResponse
    {
        Gate::authorize('organise', $event);

        $responses = FeedbackResponse::query()
            ->where('event_id', $event->getKey())
            ->get()
            ->groupBy('feedback_question_id');

        $questions = FeedbackQuestion::query()->orderBy('display_order')->orderBy('id')->get();

        return response()->json(['data' => [
            'invitations' => [
                'sent' => FeedbackInvitation::query()->where('event_id', $event->getKey())->count(),
                'submitted' => FeedbackInvitation::query()->where('event_id', $event->getKey())->whereNotNull('submitted_at')->count(),
            ],
            'questions' => $questions
                ->map(fn (FeedbackQuestion $question): array => $this->summarise($question, $responses->get($question->getKey(), collect())))
                ->all(),
        ]]);
    }

    /**
     * @param  Collection<int, FeedbackResponse>  $responses
     * @return array<string, mixed>
     */
    private function summarise(FeedbackQuestion $question, Collection $responses): array
    {
        $summary = [
            'key' => $question->key,
            'prompt' => $question->prompt,
            'type' => $question->type->value,
            'response_count' => $responses->count(),
        ];

        if ($question->type === FeedbackQuestionType::Rating) {
            $ratings = $responses->pluck('rating')->filter()->values();

            return [
                ...$summary,
                'average' => $ratings->isEmpty() ? null : round($ratings->avg(), 2),
                'distribution' => collect(range(1, 5))
                    ->mapWithKeys(fn (int $rating): array => [(string) $rating => $ratings->filter(fn (int $value): bool => $value === $rating)->count()])
                    ->all(),
            ];
        }

        return [
            ...$summary,
            // Shuffled: consecutive ids across questions would otherwise let an
            // Organiser reassemble one Player's whole submission.
            'answers' => $responses->pluck('answer')->filter()->shuffle()->values()->all(),
        ];
    }
}
