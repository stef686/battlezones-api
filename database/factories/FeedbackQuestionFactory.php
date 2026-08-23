<?php

namespace Database\Factories;

use App\Enums\FeedbackQuestionType;
use App\Models\FeedbackQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeedbackQuestion>
 */
class FeedbackQuestionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(3),
            'prompt' => fake()->sentence().'?',
            'type' => FeedbackQuestionType::Rating,
            'display_order' => 0,
        ];
    }

    public function text(): self
    {
        return $this->state(fn (): array => ['type' => FeedbackQuestionType::Text]);
    }
}
