<?php

namespace App\Models;

use Database\Factories\EventStandingScoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $event_standing_id
 * @property int $event_score_type_id
 * @property numeric $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read EventScoreType $scoreType
 * @property-read EventStanding $standing
 *
 * @method static \Database\Factories\EventStandingScoreFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStandingScore newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStandingScore newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStandingScore query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStandingScore whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStandingScore whereEventScoreTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStandingScore whereEventStandingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStandingScore whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStandingScore whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStandingScore whereValue($value)
 *
 * @mixin \Eloquent
 */
class EventStandingScore extends Model
{
    /** @use HasFactory<EventStandingScoreFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_standing_id',
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
     * @return BelongsTo<EventStanding, $this>
     */
    public function standing(): BelongsTo
    {
        return $this->belongsTo(EventStanding::class, 'event_standing_id');
    }

    /**
     * @return BelongsTo<EventScoreType, $this>
     */
    public function scoreType(): BelongsTo
    {
        return $this->belongsTo(EventScoreType::class, 'event_score_type_id');
    }
}
