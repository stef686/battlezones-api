<?php

namespace App\Models;

use App\Enums\SortDirection;
use Database\Factories\EventScoreTypeFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $event_id
 * @property string $name
 * @property string $abbreviation
 * @property string $slug
 * @property SortDirection $sort_direction
 * @property bool $is_derived
 * @property bool $is_primary
 * @property int|null $ranking_order
 * @property numeric|null $win_points
 * @property numeric|null $draw_points
 * @property numeric|null $loss_points
 * @property int $display_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Event $event
 * @property-read Collection<int, GameScore> $scores
 * @property-read int|null $scores_count
 *
 * @method static \Database\Factories\EventScoreTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScoreType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScoreType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScoreType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScoreType whereAbbreviation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScoreType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScoreType whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScoreType whereDrawPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScoreType whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScoreType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScoreType whereIsDerived($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScoreType whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScoreType whereLossPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScoreType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScoreType whereRankingOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScoreType whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScoreType whereSortDirection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScoreType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScoreType whereWinPoints($value)
 *
 * @mixin \Eloquent
 */
class EventScoreType extends Model
{
    /** @use HasFactory<EventScoreTypeFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'name',
        'abbreviation',
        'slug',
        'sort_direction',
        'is_derived',
        'is_primary',
        'ranking_order',
        'win_points',
        'draw_points',
        'loss_points',
        'display_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_direction' => SortDirection::class,
            'is_derived' => 'boolean',
            'is_primary' => 'boolean',
            'ranking_order' => 'integer',
            'win_points' => 'decimal:2',
            'draw_points' => 'decimal:2',
            'loss_points' => 'decimal:2',
            'display_order' => 'integer',
        ];
    }

    /**
     * A heading short enough to sit over a number, worked out from a name.
     *
     * The initials of a multi-word name — Match Points becomes MP — and the
     * first three letters of a single-word one, since "Kills" shortened to
     * "K" says less than "KIL" does. Used where an Organiser has not written
     * their own, and by the clients before this column existed, so an Event
     * that never touches it reads exactly as it always did.
     */
    public static function abbreviate(string $name): string
    {
        $words = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $short = count($words) > 1
            ? implode('', array_map(fn (string $word): string => mb_substr($word, 0, 1), $words))
            : mb_substr($words[0] ?? $name, 0, 3);

        return mb_strtoupper(mb_substr($short, 0, 3));
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * The scores recorded under this column.
     *
     * Read to answer whether anything would be destroyed by dropping it —
     * `game_scores` cascades on delete, so a Score Type with scores is one an
     * Organiser must not be allowed to remove.
     *
     * @return HasMany<GameScore, $this>
     */
    public function scores(): HasMany
    {
        return $this->hasMany(GameScore::class);
    }
}
