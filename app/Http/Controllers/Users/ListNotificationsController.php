<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Users', 'APIs for Users')]
#[Authenticated]
class ListNotificationsController extends Controller
{
    #[Endpoint('List Notifications', "The authenticated Player's in-app notifications, newest first, with the unread count alongside. Event notifications always arrive here, whatever the Player's channel preferences say.")]
    #[Response(['data' => [[
        'id' => '9c1f0f4e-1d2a-4f3b-9a5c-2f6f3f9e1a11',
        'data' => ['type' => 'round_published', 'event_slug' => 'london-grand-tournament', 'round_id' => 4],
        'read_at' => null,
        'created_at' => '2026-09-12T12:00:00+00:00',
    ]], 'links' => [
        'first' => 'https://api.battlezones.test/api/notifications?page=1',
        'last' => 'https://api.battlezones.test/api/notifications?page=1',
        'prev' => null,
        'next' => null,
    ], 'meta' => [
        'current_page' => 1,
        'from' => 1,
        'last_page' => 1,
        'path' => 'https://api.battlezones.test/api/notifications',
        'per_page' => 15,
        'to' => 1,
        'total' => 1,
        'unread_count' => 3,
    ]])]
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $notifications = $user->notifications()->paginate();

        return NotificationResource::collection($notifications)
            ->additional(['meta' => ['unread_count' => $user->unreadNotifications()->count()]]);
    }
}
