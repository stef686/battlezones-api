<?php

namespace App\Enums;

enum PairingFormat: string
{
    case Swiss = 'swiss';
    case RoundRobin = 'round_robin';
    case Knockout = 'knockout';
    case Random = 'random';
}
