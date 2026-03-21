<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
        ];
    }

    public function edited(): static
    {
        return $this->state(['edited_at' => now()]);
    }

    public function deleted(): static
    {
        return $this->state([
            'body' => null,
            'deleted_at' => now(),
        ]);
    }
}
