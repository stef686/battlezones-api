<?php

namespace App\Http\Requests\Events;

use App\Enums\FeedbackQuestionType;
use App\Models\FeedbackQuestion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('answers', 'object[]', 'One entry per question answered: a rating for rating questions, an answer for text questions. Unanswered questions may be left out.', required: true, example: [['question_id' => 1, 'rating' => 5], ['question_id' => 8, 'answer' => 'The missions were excellent.']])]
class SubmitFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'answers' => ['present', 'array'],
            'answers.*.question_id' => ['required', 'integer', 'distinct', 'exists:feedback_questions,id'],
            'answers.*.rating' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:5'],
            'answers.*.answer' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->validateAnswerShapes($validator),
        ];
    }

    /**
     * @return list<array{question_id: int, rating: int|null, answer: string|null}>
     */
    public function answers(): array
    {
        return collect($this->array('answers'))
            ->map(fn (array $answer): array => [
                'question_id' => (int) $answer['question_id'],
                'rating' => isset($answer['rating']) ? (int) $answer['rating'] : null,
                'answer' => isset($answer['answer']) ? (string) $answer['answer'] : null,
            ])
            ->values()
            ->all();
    }

    /**
     * A rating question takes a rating and a text question takes text, so an
     * answer in the wrong shape is a client bug rather than a silent null in
     * the dashboard.
     */
    private function validateAnswerShapes(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        /** @var Collection<int, FeedbackQuestion> $questions */
        $questions = FeedbackQuestion::query()
            ->whereIn('id', array_column($this->answers(), 'question_id'))
            ->get()
            ->keyBy('id');

        foreach ($this->answers() as $index => $answer) {
            $question = $questions->get($answer['question_id']);

            if (! $question instanceof FeedbackQuestion) {
                continue;
            }

            $expectsRating = $question->type === FeedbackQuestionType::Rating;

            if ($expectsRating && $answer['rating'] === null) {
                $validator->errors()->add("answers.{$index}.rating", 'This question is answered with a rating.');
            }

            if (! $expectsRating && $answer['answer'] === null) {
                $validator->errors()->add("answers.{$index}.answer", 'This question is answered with text.');
            }
        }
    }
}
