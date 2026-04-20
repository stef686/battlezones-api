<?php

namespace App\Models;

use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $round_id
 * @property int|null $table_number
 * @property bool $is_bye
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Round $round
 * @property-read Collection<int, EventAttendee> $attendees
 * @property-read GameAttendeePivot|null $pivot
 *
 * @method static GameFactory factory($count = null, $state = [])
 */
class Game extends Model
{
    /** @use HasFactory<GameFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'round_id',
        'table_number',
        'is_bye',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_bye' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Round, $this>
     */
    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    /**
     * @return BelongsToMany<EventAttendee, $this, GameAttendeePivot>
     */
    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(EventAttendee::class, 'game_attendee')
            ->using(GameAttendeePivot::class)
            ->withPivot('score')
            ->withTimestamps();
    }
}
