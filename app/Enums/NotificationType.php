<?php

namespace App\Enums;

enum NotificationType: string
{
    case PrimaryMessages = 'primary_messages';
    case MessageRequests = 'message_requests';
    case EventMessages = 'event_messages';
    case RoundLive = 'round_live';

    public function label(): string
    {
        return match ($this) {
            self::PrimaryMessages => 'Primary Messages',
            self::MessageRequests => 'Message Requests',
            self::EventMessages => 'Event Messages',
            self::RoundLive => 'Round Live',
        };
    }
}
