<?php

namespace App\Notifications\Conversations;

use App\Enums\NotificationType;
use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Message $message, public User $sender) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        /** @var User $notifiable */
        return array_map(
            fn ($channel) => $channel->value,
            $notifiable->getNotificationChannels(NotificationType::PrimaryMessages),
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("New message from {$this->sender->public_name}")
            ->greeting('Hello!')
            ->line("{$this->sender->public_name} sent you a new message.")
            ->salutation('The Battlezones Team');
    }
}
