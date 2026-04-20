<?php

namespace App\Models;

use Database\Factories\RoundFactory;
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Event $event
 * @property-read Collection<int, Game> $games
 *
 * @method static RoundFactory factory($count = null, $state = [])
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
    ];

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
