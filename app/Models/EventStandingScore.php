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
 * @property string $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read EventStanding $standing
 * @property-read EventScoreType $scoreType
 *
 * @method static EventStandingScoreFactory factory($count = null, $state = [])
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
