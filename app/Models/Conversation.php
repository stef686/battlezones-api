<?php

namespace App\Models;

use App\Enums\ConversationTab;
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
 * @property string|null $name
 * @property bool $is_group
 * @property int|null $event_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Message|null $latestMessage
 * @property-read Collection<int, Message> $messages
 * @property-read int|null $messages_count
 * @property-read Collection<int, User> $users
 * @property-read int|null $users_count
 *
 * @method static Builder<static>|Conversation archivedForUser(int $userId)
 * @method static Builder<static>|Conversation eventsForUser(int $userId)
 * @method static \Database\Factories\ConversationFactory factory($count = null, $state = [])
 * @method static Builder<static>|Conversation forTab(\App\Enums\ConversationTab $tab, int $userId)
 * @method static Builder<static>|Conversation forUser(int $userId)
 * @method static Builder<static>|Conversation newModelQuery()
 * @method static Builder<static>|Conversation newQuery()
 * @method static Builder<static>|Conversation primaryForUser(int $userId)
 * @method static Builder<static>|Conversation query()
 * @method static Builder<static>|Conversation requestsForUser(int $userId)
 * @method static Builder<static>|Conversation whereCreatedAt($value)
 * @method static Builder<static>|Conversation whereEventId($value)
 * @method static Builder<static>|Conversation whereId($value)
 * @method static Builder<static>|Conversation whereIsGroup($value)
 * @method static Builder<static>|Conversation whereName($value)
 * @method static Builder<static>|Conversation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Conversation extends Model
{
    /** @use HasFactory<ConversationFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'is_group',
        'event_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_group' => 'boolean',
        ];
    }

    public function isGroup(): bool
    {
        return $this->is_group;
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('last_read_at', 'visible_from', 'deleted_at', 'archived_at')
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
    public function scopeForTab(Builder $query, ConversationTab $tab, int $userId): Builder
    {
        return match ($tab) {
            ConversationTab::Primary => $query->primaryForUser($userId),
            ConversationTab::Events => $query->eventsForUser($userId),
            ConversationTab::Requests => $query->requestsForUser($userId),
            ConversationTab::Archived => $query->archivedForUser($userId),
        };
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
    public function scopePrimaryForUser(Builder $query, int $userId): Builder
    {
        return $query->forUser($userId)
            ->whereNull('event_id')
            ->where(function (Builder $q) use ($userId) {
                $q->where('is_group', true)
                    ->orWhereRaw(self::firstMessageSenderSql('='), [$userId])
                    ->orWhereHas('users', self::otherUserFollowedBy($userId));
            });
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeEventsForUser(Builder $query, int $userId): Builder
    {
        return $query->forUser($userId)->whereNotNull('event_id');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeRequestsForUser(Builder $query, int $userId): Builder
    {
        return $query->forUser($userId)
            ->whereNull('event_id')
            ->where('is_group', false)
            ->whereRaw(self::firstMessageSenderSql('!='), [$userId])
            ->whereDoesntHave('users', self::otherUserFollowedBy($userId));
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

    /**
     * @return \Closure(Builder): void
     */
    private static function otherUserFollowedBy(int $userId): \Closure
    {
        return function (Builder $q) use ($userId) {
            $q->where('conversation_user.user_id', '!=', $userId)
                ->whereIn('conversation_user.user_id', function ($sub) use ($userId) {
                    $sub->select('following_id')
                        ->from('follows')
                        ->where('follower_id', $userId);
                });
        };
    }

    private static function firstMessageSenderSql(string $operator): string
    {
        return "(select user_id from messages where messages.conversation_id = conversations.id order by created_at asc limit 1) {$operator} ?";
    }

    public static function findBetween(int $userA, int $userB): ?self
    {
        return self::query()
            ->where('is_group', false)
            ->whereHas('users', fn (Builder $q) => $q->where('conversation_user.user_id', $userA))
            ->whereHas('users', fn (Builder $q) => $q->where('conversation_user.user_id', $userB))
            ->first();
    }
}
