<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $game_id
 * @property int $event_attendee_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameAttendeePivot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameAttendeePivot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameAttendeePivot query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameAttendeePivot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameAttendeePivot whereEventAttendeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameAttendeePivot whereGameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameAttendeePivot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameAttendeePivot whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class GameAttendeePivot extends Pivot
{
    protected $table = 'game_attendee';
}
