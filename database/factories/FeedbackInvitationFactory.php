<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\FeedbackInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<FeedbackInvitation>
 */
class FeedbackInvitationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'token' => FeedbackInvitation::hashToken(Str::random(64)),
            'sent_at' => now(),
            'expires_at' => now()->addDays(FeedbackInvitation::LIFETIME_DAYS),
            'submitted_at' => null,
        ];
    }

    public function expired(): self
    {
        return $this->state(fn (): array => [
            'sent_at' => now()->subDays(FeedbackInvitation::LIFETIME_DAYS + 1),
            'expires_at' => now()->subDay(),
        ]);
    }
}
