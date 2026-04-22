<?php

namespace App\Models;

use Database\Factories\GameScoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $game_id
 * @property int $event_attendee_id
 * @property int $event_score_type_id
 * @property numeric $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read EventAttendee $attendee
 * @property-read Game $game
 * @property-read EventScoreType $scoreType
 *
 * @method static \Database\Factories\GameScoreFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameScore newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameScore newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameScore query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameScore whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameScore whereEventAttendeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameScore whereEventScoreTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameScore whereGameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameScore whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameScore whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameScore whereValue($value)
 *
 * @mixin \Eloquent
 */
class GameScore extends Model
{
    /** @use HasFactory<GameScoreFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'game_id',
        'event_attendee_id',
        'event_score_type_id',
        'value',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Game, $this>
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * @return BelongsTo<EventAttendee, $this>
     */
    public function attendee(): BelongsTo
    {
        return $this->belongsTo(EventAttendee::class, 'event_attendee_id');
    }

    /**
     * @return BelongsTo<EventScoreType, $this>
     */
    public function scoreType(): BelongsTo
    {
        return $this->belongsTo(EventScoreType::class, 'event_score_type_id');
    }
}
