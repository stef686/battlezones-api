<?php

namespace App\Models;

use Database\Factories\RoundFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $event_id
 * @property int $number
 * @property string|null $name
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Event $event
 * @property-read Collection<int, Game> $games
 * @property-read int|null $games_count
 *
 * @method static \Database\Factories\RoundFactory factory($count = null, $state = [])
 * @method static Builder<static>|Round live()
 * @method static Builder<static>|Round newModelQuery()
 * @method static Builder<static>|Round newQuery()
 * @method static Builder<static>|Round query()
 * @method static Builder<static>|Round whereCreatedAt($value)
 * @method static Builder<static>|Round whereEventId($value)
 * @method static Builder<static>|Round whereId($value)
 * @method static Builder<static>|Round whereName($value)
 * @method static Builder<static>|Round whereNumber($value)
 * @method static Builder<static>|Round wherePublishedAt($value)
 * @method static Builder<static>|Round whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Round extends Model
{
    /** @use HasFactory<RoundFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'number',
        'name',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * Whether Players can see this Round's Games.
     *
     * Live is a latch: earlier Rounds stay Live as later ones are published.
     */
    public function isLive(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeLive(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return HasMany<Game, $this>
     */
    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }
}
