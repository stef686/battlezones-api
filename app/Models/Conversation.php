<?php

namespace App\Models;

use Database\Factories\ConversationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Message|null $latestMessage
 * @property-read Collection<int, Message> $messages
 * @property-read int|null $messages_count
 * @property-read Collection<int, User> $users
 * @property-read int|null $users_count
 *
 * @method static \Database\Factories\ConversationFactory factory($count = null, $state = [])
 * @method static Builder<static>|Conversation archivedForUser(int $userId)
 * @method static Builder<static>|Conversation forUser(int $userId)
 * @method static Builder<static>|Conversation newModelQuery()
 * @method static Builder<static>|Conversation newQuery()
 * @method static Builder<static>|Conversation query()
 * @method static Builder<static>|Conversation whereCreatedAt($value)
 * @method static Builder<static>|Conversation whereId($value)
 * @method static Builder<static>|Conversation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Conversation extends Model
{
    /** @use HasFactory<ConversationFactory> */
    use HasFactory;

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('last_read_at', 'deleted_at', 'archived_at')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Message, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * @return HasOne<Message, $this>
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->whereHas('users', function (Builder $q) use ($userId) {
            $q->where('conversation_user.user_id', $userId)
                ->whereNull('conversation_user.deleted_at')
                ->whereNull('conversation_user.archived_at');
        });
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeArchivedForUser(Builder $query, int $userId): Builder
    {
        return $query->whereHas('users', function (Builder $q) use ($userId) {
            $q->where('conversation_user.user_id', $userId)
                ->whereNull('conversation_user.deleted_at')
                ->whereNotNull('conversation_user.archived_at');
        });
    }

    public static function findBetween(int $userA, int $userB): ?self
    {
        return self::query()
            ->whereHas('users', fn (Builder $q) => $q->where('conversation_user.user_id', $userA))
            ->whereHas('users', fn (Builder $q) => $q->where('conversation_user.user_id', $userB))
            ->first();
    }
}
