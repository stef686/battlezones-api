<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [];
    }

    public function group(string $name = 'Test Group'): static
    {
        return $this->state([
            'is_group' => true,
            'name' => $name,
        ]);
    }

    public function withUsers(User $userA, User $userB): static
    {
        return $this->afterCreating(function (Conversation $conversation) use ($userA, $userB) {
            $conversation->users()->attach([$userA->id, $userB->id]);
        });
    }
}
