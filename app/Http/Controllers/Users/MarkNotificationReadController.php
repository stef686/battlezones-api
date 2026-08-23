<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Users', 'APIs for Users')]
#[Authenticated]
class MarkNotificationReadController extends Controller
{
    #[Endpoint('Mark a Notification Read', "Scoped to the authenticated Player: another Player's notification is not found rather than forbidden.")]
    #[UrlParam('notification', 'string', 'The id of the notification.', example: '9b1d4d0a-1f5f-4a8c-9c1e-2b6f2b3f0a11')]
    #[Response(['data' => [
        'id' => '9c1f0f4e-1d2a-4f3b-9a5c-2f6f3f9e1a11',
        'data' => ['type' => 'round_published', 'event_slug' => 'london-grand-tournament', 'round_id' => 4],
        'read_at' => '2026-09-12T12:01:00+00:00',
        'created_at' => '2026-09-12T12:00:00+00:00',
    ]])]
    public function __invoke(Request $request, string $notification): NotificationResource
    {
        $found = $request->user()->notifications()->find($notification);

        abort_unless($found instanceof DatabaseNotification, 404);

        $found->markAsRead();

        return NotificationResource::make($found);
    }
}
