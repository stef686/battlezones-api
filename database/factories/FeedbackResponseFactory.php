<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\FeedbackQuestion;
use App\Models\FeedbackResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeedbackResponse>
 */
class FeedbackResponseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'feedback_question_id' => FeedbackQuestion::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'answer' => null,
        ];
    }
}
