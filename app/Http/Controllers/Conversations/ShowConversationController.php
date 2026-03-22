<?php

namespace App\Http\Controllers\Conversations;

use App\Http\Controllers\Controller;
use App\Http\Resources\Conversations\MessageResource;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ShowConversationController extends Controller
{
    public function __invoke(Request $request, Conversation $conversation): AnonymousResourceCollection
    {
        Gate::authorize('view', $conversation);

        $userId = $request->user()->id;
        $pivot = $conversation->users()->wherePivot('user_id', $userId)->first()?->pivot;
        $deletedAt = $pivot?->getAttribute('deleted_at');
        $visibleFrom = $pivot?->getAttribute('visible_from');

        $messages = $conversation->messages()
            ->when($deletedAt, fn ($q, $deletedAt) => $q->where('created_at', '>', $deletedAt))
            ->when($visibleFrom, fn ($q, $visibleFrom) => $q->where('created_at', '>=', $visibleFrom))
            ->oldest('created_at')
            ->paginate();

        $conversation->users()->updateExistingPivot($userId, [
            'last_read_at' => now(),
        ]);

        return MessageResource::collection($messages);
    }
}
