<?php

namespace App\Http\Controllers\Conversations;

use App\Actions\Conversations\RemoveGroupMember;
use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\RemoveGroupMemberRequest;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Response;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response as ScribeResponse;

#[Group('Conversations', 'APIs for Conversations')]
class RemoveGroupMemberController extends Controller
{
    #[Endpoint('Remove Group Member', 'Remove a member from a group conversation.')]
    #[ScribeResponse(content: '', status: 204)]
    public function __invoke(RemoveGroupMemberRequest $request, Conversation $conversation, User $user, RemoveGroupMember $action): Response
    {
        $action->execute($request->user(), $conversation, $user);

        return response()->noContent();
    }
}
