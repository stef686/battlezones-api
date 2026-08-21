<?php

namespace App\Enums;

/**
 * Who can see a Round's Games.
 *
 * There is deliberately no completed state: round completeness is derived from
 * Games having scores, and a stored flag can disagree with the results it
 * claims to summarise.
 */
enum RoundStatus: string
{
    case Draft = 'draft';
    case Live = 'live';
}
