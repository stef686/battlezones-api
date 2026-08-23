<?php

namespace App\Enums;

enum NotificationType: string
{
    case PrimaryMessages = 'primary_messages';
    case MessageRequests = 'message_requests';
    case EventMessages = 'event_messages';
    case RoundLive = 'round_live';
    case ResultActivity = 'result_activity';
    case VotingOpen = 'voting_open';

    /**
     * Whether this is an Event notification, which always reaches the app.
     *
     * You opted in by registering, and a result submitted with no opponent
     * confirmation makes the notification the only backstop against a wrong
     * score going unnoticed — so preferences add channels here rather than
     * deciding whether you are told at all.
     */
    public function alwaysInApp(): bool
    {
        return match ($this) {
            self::RoundLive, self::ResultActivity, self::VotingOpen => true,
            self::PrimaryMessages, self::MessageRequests, self::EventMessages => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::PrimaryMessages => 'Primary Messages',
            self::MessageRequests => 'Message Requests',
            self::EventMessages => 'Event Messages',
            self::RoundLive => 'Round Live',
            self::ResultActivity => 'Result Activity',
            self::VotingOpen => 'Voting Open',
        };
    }
}
