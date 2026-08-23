<?php

namespace Database\Seeders;

use App\Enums\FeedbackQuestionType;
use App\Models\FeedbackQuestion;
use Illuminate\Database\Seeder;

/**
 * The fixed post-event question set.
 *
 * Rewording a prompt is a seeder run rather than a deploy, and the `key` is
 * what a response is grouped by, so a reworded question keeps its history.
 */
class FeedbackQuestionSeeder extends Seeder
{
    /**
     * @var list<array{key: string, prompt: string, type: FeedbackQuestionType}>
     */
    private const QUESTIONS = [
        ['key' => 'overall', 'prompt' => 'Overall, how was the event?', 'type' => FeedbackQuestionType::Rating],
        ['key' => 'venue', 'prompt' => 'How were the venue and the tables?', 'type' => FeedbackQuestionType::Rating],
        ['key' => 'organisation', 'prompt' => 'How well did the day run — timings, pairings, results?', 'type' => FeedbackQuestionType::Rating],
        ['key' => 'missions', 'prompt' => 'How were the missions and the rules pack?', 'type' => FeedbackQuestionType::Rating],
        ['key' => 'opponents', 'prompt' => 'How were your games and your opponents?', 'type' => FeedbackQuestionType::Rating],
        ['key' => 'value', 'prompt' => 'Was the ticket good value?', 'type' => FeedbackQuestionType::Rating],
        ['key' => 'return', 'prompt' => 'How likely are you to come back?', 'type' => FeedbackQuestionType::Rating],
        ['key' => 'best_thing', 'prompt' => 'What was the best thing about the event?', 'type' => FeedbackQuestionType::Text],
        ['key' => 'improve', 'prompt' => 'What would you change next time?', 'type' => FeedbackQuestionType::Text],
        ['key' => 'anything_else', 'prompt' => 'Anything else you want the organisers to know?', 'type' => FeedbackQuestionType::Text],
    ];

    public function run(): void
    {
        foreach (self::QUESTIONS as $order => $question) {
            FeedbackQuestion::query()->updateOrCreate(
                ['key' => $question['key']],
                [
                    'prompt' => $question['prompt'],
                    'type' => $question['type'],
                    'display_order' => $order,
                ],
            );
        }
    }
}
