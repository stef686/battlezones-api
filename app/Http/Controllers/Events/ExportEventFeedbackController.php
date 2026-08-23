<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\FeedbackQuestion;
use App\Models\FeedbackResponse;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Group('Events', 'APIs for Events')]
#[Authenticated]
class ExportEventFeedbackController extends Controller
{
    #[Endpoint('Export Feedback', 'Organisers only. A CSV of every answer, grouped by question and shuffled within it, so no row can be tied to a Player or to another row. Synchronous: one Event is a few hundred rows.')]
    #[UrlParam('event', 'string', 'The slug of the event.', example: 'london-grand-tournament')]
    #[Response(content: "question_key,prompt,type,rating,answer\noverall,How was the Event overall?,rating,5,\n", description: 'A CSV download.')]
    public function __invoke(Event $event): StreamedResponse
    {
        Gate::authorize('organise', $event);

        $questions = FeedbackQuestion::query()->orderBy('display_order')->orderBy('id')->get();

        $responses = FeedbackResponse::query()
            ->where('event_id', $event->getKey())
            ->get()
            ->groupBy('feedback_question_id');

        return response()->streamDownload(function () use ($questions, $responses): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['question_key', 'prompt', 'type', 'rating', 'answer']);

            foreach ($questions as $question) {
                foreach ($responses->get($question->getKey(), collect())->shuffle() as $response) {
                    fputcsv($handle, [
                        $question->key,
                        $question->prompt,
                        $question->type->value,
                        $response->rating,
                        $response->answer,
                    ]);
                }
            }

            fclose($handle);
        }, "{$event->slug}-feedback.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
