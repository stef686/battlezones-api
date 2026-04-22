<?php

namespace App\Queries;

use App\Enums\ConversationTab;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ConversationListQuery
{
    private ?ConversationTab $tab = null;

    private function __construct(private int $userId) {}

    public static function forUser(int $userId): self
    {
        return new self($userId);
    }

    public function tab(ConversationTab $tab): self
    {
        $this->tab = $tab;

        return $this;
    }

    /**
     * @return Builder<Conversation>
     */
    public function toQuery(): Builder
    {
        $query = $this->baseQuery();

        return match ($this->tab) {
            ConversationTab::Primary => $this->applyPrimary($query),
            ConversationTab::Events => $this->applyEvents($query),
            ConversationTab::Requests => $this->applyRequests($query),
            ConversationTab::Archived => $this->applyArchived(),
            default => $query,
        };
    }

    /**
     * @return Builder<Conversation>
     */
    private function baseQuery(): Builder
    {
        return Conversation::query()
            ->whereHas('users', function (Builder $q) {
                $q->where('conversation_user.user_id', $this->userId)
                    ->whereNull('conversation_user.deleted_at')
                    ->whereNull('conversation_user.archived_at');
            });
    }

    /**
     * @param  Builder<Conversation>  $query
     * @return Builder<Conversation>
     */
    private function applyPrimary(Builder $query): Builder
    {
        return $query
            ->whereNull('event_id')
            ->where(function (Builder $q) {
                $q->where('is_group', true)
                    ->orWhere($this->firstMessageSender(), $this->userId)
                    ->orWhereHas('users', $this->otherUserFollowedByAuthUser());
            });
    }

    private function firstMessageSender(): Expression
    {
        $subquery = Message::query()
            ->whereColumn('messages.conversation_id', 'conversations.id')
            ->oldest()
            ->select('user_id')
            ->limit(1);

        return DB::raw("({$subquery->toSql()})");
    }

    /**
     * @param  Builder<Conversation>  $query
     * @return Builder<Conversation>
     */
    private function applyEvents(Builder $query): Builder
    {
        return $query->whereNotNull('event_id');
    }

    /**
     * @param  Builder<Conversation>  $query
     * @return Builder<Conversation>
     */
    private function applyRequests(Builder $query): Builder
    {
        return $query
            ->whereNull('event_id')
            ->where('is_group', false)
            ->where($this->firstMessageSender(), '!=', $this->userId)
            ->whereDoesntHave('users', $this->otherUserFollowedByAuthUser());
    }

    /**
     * @return Builder<Conversation>
     */
    private function applyArchived(): Builder
    {
        return Conversation::query()
            ->whereHas('users', function (Builder $q) {
                $q->where('conversation_user.user_id', $this->userId)
                    ->whereNull('conversation_user.deleted_at')
                    ->whereNotNull('conversation_user.archived_at');
            });
    }

    private function otherUserFollowedByAuthUser(): \Closure
    {
        return function (Builder $q) {
            $q->where('conversation_user.user_id', '!=', $this->userId)
                ->whereIn('conversation_user.user_id', function ($sub) {
                    $sub->select('following_id')
                        ->from('follows')
                        ->where('follower_id', $this->userId);
                });
        };
    }
}
