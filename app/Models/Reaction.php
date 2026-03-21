<?php

namespace App\Models;

use Database\Factories\ReactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $reactable_id
 * @property string $reactable_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model $reactable
 * @property-read User $user
 *
 * @method static \Database\Factories\ReactionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reaction whereReactableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reaction whereReactableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reaction whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Reaction extends Model
{
    /** @use HasFactory<ReactionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function reactable(): MorphTo
    {
        return $this->morphTo();
    }
}
