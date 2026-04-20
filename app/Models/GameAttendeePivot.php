<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int|null $score
 */
class GameAttendeePivot extends Pivot
{
    protected $table = 'game_attendee';
}
