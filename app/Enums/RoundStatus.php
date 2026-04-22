<?php

namespace App\Enums;

enum RoundStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Completed = 'completed';
}
