<?php

namespace App\Enums;

enum ConversationTab: string
{
    case Primary = 'primary';
    case Events = 'events';
    case Requests = 'requests';
    case Archived = 'archived';
}
