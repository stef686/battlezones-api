<?php

namespace App\Notifications\Events;

use App\Models\Event;
use App\Services\Frontend;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The post-event feedback form, with this Player's own link.
 *
 * Transactional: it is the delivery mechanism rather than a notification about
 * something already on screen, so it sits outside the preference system.
 * Gating it would let a Player switch off their only way in.
 */
class FeedbackRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Event $event,
        #[\SensitiveParameter] public string $plainToken,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = Frontend::feedbackUrl($this->plainToken);

        return (new MailMessage())
            ->subject("How was {$this->event->name}?")
            ->line("Thanks for playing at {$this->event->name}.")
            ->line('The organisers would like to know how it went. Your answers are anonymous — nothing you write is stored against your name.')
            ->action('Give feedback', $url)
            ->line('This link works for 30 days and can only be used once.')
            ->salutation('The Battlezones Team');
    }
}
