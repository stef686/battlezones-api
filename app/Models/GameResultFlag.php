<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A claim that a Game's submitted result is wrong.
 *
 * At most one flag is open per Game: flagging again while one is unresolved
 * is a no-op, and a fresh flag is only possible once an Organiser has closed
 * the previous one.
 *
 * @property int $id
 * @property int $game_id
 * @property int $flagged_by_user_id
 * @property string|null $reason
 * @property Carbon|null $resolved_at
 * @property int|null $resolved_by_user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $flaggedBy
 * @property-read Game $game
 * @property-read User|null $resolvedBy
 *
 * @method static Builder<static>|GameResultFlag newModelQuery()
 * @method static Builder<static>|GameResultFlag newQuery()
 * @method static Builder<static>|GameResultFlag query()
 * @method static Builder<static>|GameResultFlag unresolved()
 * @method static Builder<static>|GameResultFlag whereCreatedAt($value)
 * @method static Builder<static>|GameResultFlag whereFlaggedByUserId($value)
 * @method static Builder<static>|GameResultFlag whereGameId($value)
 * @method static Builder<static>|GameResultFlag whereId($value)
 * @method static Builder<static>|GameResultFlag whereReason($value)
 * @method static Builder<static>|GameResultFlag whereResolvedAt($value)
 * @method static Builder<static>|GameResultFlag whereResolvedByUserId($value)
 * @method static Builder<static>|GameResultFlag whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class GameResultFlag extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'game_id',
        'flagged_by_user_id',
        'reason',
        'resolved_at',
        'resolved_by_user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeUnresolved(Builder $query): void
    {
        $query->whereNull('resolved_at');
    }

    /**
     * @return BelongsTo<Game, $this>
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function flaggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'flagged_by_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }
}
