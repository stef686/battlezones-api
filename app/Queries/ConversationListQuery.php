<?php

namespace App\Queries;

use App\Enums\ConversationTab;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ConversationListQuery
{
    private const EPOCH = '1970-01-01 00:00:00';

    private ConversationTab $tab;

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
        $query = $this->applyTab();

        return $query
            ->with(['users', 'latestMessage'])
            ->addSelect([
                'latest_message_at' => Message::query()
                    ->whereColumn('conversation_id', 'conversations.id')
                    ->latest()
                    ->select('created_at')
                    ->limit(1),
                'unread_count' => $this->unreadCountSubquery(),
            ])
            ->orderByDesc('latest_message_at');
    }

    /**
     * @return LengthAwarePaginator<int, Conversation>
     */
    public function paginate(): LengthAwarePaginator
    {
        return $this->toQuery()->paginate();
    }

    /**
     * @return Builder<Conversation>
     */
    private function applyTab(): Builder
    {
        $query = $this->baseQuery();

        return match ($this->tab) {
            ConversationTab::Primary => $this->applyPrimary($query),
            ConversationTab::Events => $this->applyEvents($query),
            ConversationTab::Requests => $this->applyRequests($query),
            ConversationTab::Archived => $this->applyArchived(),
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

        return DB::raw("({$subquery->toRawSql()})");
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

    /**
     * @return Builder<Message>
     */
    private function unreadCountSubquery(): Builder
    {
        $lastReadAt = DB::table('conversation_user')
            ->whereColumn('conversation_user.conversation_id', 'conversations.id')
            ->where('conversation_user.user_id', $this->userId)
            ->select('last_read_at')
            ->limit(1);

        return Message::query()
            ->selectRaw('count(*)')
            ->whereColumn('messages.conversation_id', 'conversations.id')
            ->where('messages.user_id', '!=', $this->userId)
            ->where('messages.created_at', '>', DB::raw(
                'coalesce(('.$lastReadAt->toSql().'), ?)'
            ))
            ->addBinding([...$lastReadAt->getBindings(), self::EPOCH]);
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
